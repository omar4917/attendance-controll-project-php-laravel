<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceRecordController extends Controller
{
    public function index(Request $request, DjangoApi $api)
    {
        $filters = $request->only(['search', 'date', 'status', 'department', 'designation']);
        
        // Fetch records with filters
        $data = $api->attendanceList($filters);
        $records = $data['attendance'] ?? [];
        
        // Fetch employees for filter dropdowns (Departments/Designations)
        $empData = $api->employees();
        $employees = $empData['employees'] ?? [];
        
        $departments = collect($employees)->pluck('department')->filter(fn($v) => !empty($v))->unique()->values()->all();
        $designations = collect($employees)->pluck('designation')->filter(fn($v) => !empty($v))->unique()->values()->all();
        
        return view('attendance_records.index', compact('records', 'departments', 'designations'));
    }

    public function create(DjangoApi $api)
    {
        // Fetch employees and shifts for the dropdown
        $employeesData = $api->employees();
        $employees = $employeesData['employees'] ?? [];
        
        $shiftsData = $api->shifts();
        $shifts = $shiftsData['shifts'] ?? [];
        
        if (request('popup')) {
            return view('attendance_records.popup', compact('employees', 'shifts'));
        }
        return view('attendance_records.form', compact('employees', 'shifts'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only([
            'employee_id', 'date', 'status', 'checkin_time', 'checkout_time', 
            'shift_id', 'device_id', 'late_override_status', 'is_status_override'
        ]);
        
        // Handle checkbox
        $payload['is_status_override'] = $request->has('is_status_override') ? 'true' : 'false';
        
        // Format times if present
        if (!empty($payload['checkin_time'])) {
            $payload['checkin_time'] = $payload['date'] . ' ' . $payload['checkin_time'];
        }
        if (!empty($payload['checkout_time'])) {
            $payload['checkout_time'] = $payload['date'] . ' ' . $payload['checkout_time'];
        }

        $files = [];
        if ($request->hasFile('checkin_image')) {
            $files['checkin_image'] = $request->file('checkin_image');
        }
        if ($request->hasFile('checkout_image')) {
            $files['checkout_image'] = $request->file('checkout_image');
        }

        $resp = $api->upsertAttendance($payload, $files);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        // If in popup mode, we might want to close the popup or show success in it.
        // For now, redirecting back to the form (popup view) with success message is easiest
        // so the user sees "Saved" inside the iframe.
        if ($request->has('popup')) {
             return redirect()->route('attendance-records.edit', ['id' => $resp['id'] ?? 0, 'popup' => 1])->with('success', 'Record saved');
        }
        
        if ($request->has('save_and_add_another')) {
            return redirect()->route('attendance-records.create')->with('success', 'Record saved');
        }
        
        if ($request->has('save_and_continue')) {
            return redirect()->route('attendance-records.edit', $resp['id'] ?? 0)->with('success', 'Record saved');
        }

        return redirect()->route('attendance.index')->with('success', 'Record saved');
    }

    public function edit($id, DjangoApi $api)
    {
        $employeesData = $api->employees();
        $employees = $employeesData['employees'] ?? [];
        
        $shiftsData = $api->shifts();
        $shifts = $shiftsData['shifts'] ?? [];
        
        // Fetch specific record
        $record = $api->getAttendance($id);
        
        if (!$record) {
             // Fallback to query params if not found
            $record = [
                'id' => $id,
                'employee_id' => request('employee_id'),
                'date' => request('date'),
                'status' => request('status'),
                'checkin_time' => request('checkin_time'),
                'checkout_time' => request('checkout_time'),
            ];
        }

        if (request('popup')) {
            return view('attendance_records.popup', compact('employees', 'shifts', 'record'));
        }
        return view('attendance_records.form', compact('employees', 'shifts', 'record'));
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        // Debug: Log what we receive
        \Log::info('Is status override checkbox: ', [
            'has_field' => $request->has('is_status_override'),
            'value' => $request->input('is_status_override'),
        ]);
        
        $payload = $request->only([
            'employee_id', 'date', 'status', 'checkin_time', 'checkout_time', 
            'shift_id', 'device_id', 'late_override_status', 'is_status_override'
        ]);
        $payload['id'] = $id;
        
        // Handle checkbox - convert 0/1 to false/true strings
        $payload['is_status_override'] = $request->input('is_status_override') == '1' ? 'true' : 'false';
        
        // Format times if present
        if (!empty($payload['checkin_time']) && strlen($payload['checkin_time']) <= 8) {
             $payload['checkin_time'] = $payload['date'] . ' ' . $payload['checkin_time'];
        }
        if (!empty($payload['checkout_time']) && strlen($payload['checkout_time']) <= 8) {
             $payload['checkout_time'] = $payload['date'] . ' ' . $payload['checkout_time'];
        }

        $files = [];
        if ($request->hasFile('checkin_image')) {
            $files['checkin_image'] = $request->file('checkin_image');
        }
        if ($request->hasFile('checkout_image')) {
            $files['checkout_image'] = $request->file('checkout_image');
        }

        $resp = $api->upsertAttendance($payload, $files);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        if ($request->has('popup')) {
             return redirect()->route('attendance-records.edit', ['id' => $id, 'popup' => 1])->with('success', 'Record updated');
        }
        
        if ($request->has('save_and_add_another')) {
            return redirect()->route('attendance-records.create')->with('success', 'Record updated');
        }
        
        if ($request->has('save_and_continue')) {
            return redirect()->back()->with('success', 'Record updated');
        }

        return redirect()->route('attendance.index')->with('success', 'Record updated');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteAttendance($id);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->route('attendance.index')->with('success', 'Record deleted');
    }

    public function bulkAction(Request $request, DjangoApi $api)
    {
        $action = $request->input('action');
        $ids = $request->input('selected_ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No records selected.');
        }

        if (!$action) {
            return redirect()->back()->with('error', 'No action selected.');
        }

        $resp = $api->bulkAction($action, $ids);

        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }

        return redirect()->back()->with('success', $resp['message'] ?? 'Bulk action completed.');
    }
}
