<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(DjangoApi $api)
    {
        $data = $api->salaryStatistics();
        $stats = $data['statistics'] ?? ($data['data'] ?? []);
        $error = $data['error'] ?? null;
        return view('salary.index', compact('stats', 'error'));
    }

    public function edit($id, DjangoApi $api)
    {
        // Fetch single salary record
        $data = $api->salaryStatistics();
        $stats = $data['statistics'] ?? ($data['data'] ?? []);
        $salary = collect($stats)->firstWhere('id', $id);
        
        if (!$salary) {
            return redirect()->route('salary.index')->with('error', 'Salary record not found');
        }

        // Fetch employees for dropdown
        $employeesData = $api->employees();
        $employees = $employeesData['employees'] ?? [];
        
        $error = $data['error'] ?? null;
        return view('salary.edit', compact('salary', 'employees', 'error'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only(['id','employee_id','month','year','gross_salary','payable']);
        $resp = $api->upsertSalaryStatistic($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('salary.index')->with('success', 'Salary statistic saved');
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->only(['employee_id','month','year','gross_salary','payable']);
        $payload['id'] = $id;
        $resp = $api->upsertSalaryStatistic($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('salary.index')->with('success', 'Salary statistic updated');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteSalaryStatistic($id);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->route('salary.index')->with('success', 'Salary statistic deleted');
    }
    public function defaults(DjangoApi $api)
    {
        $data = $api->salaryDefaults();
        $defaults = $data['defaults'] ?? [];
        $error = $data['error'] ?? null;
        return view('salary.defaults', compact('defaults', 'error'));
    }

    public function saveDefaults(Request $request, DjangoApi $api)
    {
        $payload = $request->except('_token');
        $resp = $api->saveSalaryDefaults($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('salary.defaults')->with('success', 'Defaults saved');
    }
}
