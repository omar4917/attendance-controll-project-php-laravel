<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request, DjangoApi $api)
    {
        $data = $api->employees();
        $employees = $data['employees'] ?? [];
        $error = $data['error'] ?? null;

        // Extract unique departments and designations for filters
        $departments = collect($employees)->pluck('department')->filter()->unique()->values()->all();
        $designations = collect($employees)->pluck('designation')->filter()->unique()->values()->all();

        // Filter by active status
        if ($request->has('active') && $request->input('active') !== '') {
            $isActive = $request->input('active') === 'yes';
            $employees = collect($employees)->filter(function ($emp) use ($isActive) {
                return ($emp['is_active'] ?? false) === $isActive;
            })->values()->all();
        }

        // Filter by Department
        if ($request->has('department') && $request->input('department') !== 'All') {
            $dept = $request->input('department');
            $employees = collect($employees)->filter(function ($emp) use ($dept) {
                return ($emp['department'] ?? '') === $dept;
            })->values()->all();
        }

        // Filter by Designation
        if ($request->has('designation') && $request->input('designation') !== 'All') {
            $desig = $request->input('designation');
            $employees = collect($employees)->filter(function ($emp) use ($desig) {
                return ($emp['designation'] ?? '') === $desig;
            })->values()->all();
        }

        $editing = [
            'employee_id' => $request->query('employee_id'),
            'name' => $request->query('name'),
            'department' => $request->query('department'),
            'designation' => $request->query('designation'),
            'email' => $request->query('email'),
            'phone' => $request->query('phone'),
        ];
        return view('employees.index', compact('employees', 'error', 'editing', 'departments', 'designations'));
    }

    public function create()
    {
        return view('employees.form');
    }

    public function edit($id, DjangoApi $api)
    {
        $data = $api->employees();
        $employees = $data['employees'] ?? [];
        $employee = collect($employees)->firstWhere('id', $id);
        
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }
        
        return view('employees.form', compact('employee'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only([
            'id','employee_id','name','email','department','phone','designation',
            'monthly_salary', 'is_active', 'hire_date', 'bank_account', 'branch', 'date_inactive'
        ]);
        
        // Handle checkbox
        $payload['is_active'] = $request->has('is_active');
        
        $resp = $api->upsertEmployee($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        if ($request->has('popup')) {
             return "<script>window.parent.location.reload();</script>";
        }
        
        return redirect()->route('employees.index')->with('success', 'Employee saved');
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->only([
            'employee_id','name','email','department','phone','designation',
            'monthly_salary', 'is_active', 'hire_date', 'bank_account', 'branch', 'date_inactive'
        ]);
        $payload['id'] = $id;
        
        // Handle checkbox
        $payload['is_active'] = $request->has('is_active');
        
        $resp = $api->upsertEmployee($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        if ($request->has('popup')) {
             return "<script>window.parent.location.reload();</script>";
        }

        return redirect()->route('employees.index')->with('success', 'Employee updated');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteEmployee($id);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->route('employees.index')->with('success', 'Employee deleted');
    }
}
