<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceRecordController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        $filters = $request->only(['search', 'date', 'status', 'department', 'designation', 'organization_id']);
        
        // Role detection
        $userRole = \Session::get('user_role', 'org_admin');
        $isSuperAdmin = $userRole === 'super_admin';
        
        // For super admins, get list of organizations
        $organizations = [];
        $selectedOrgId = $request->input('organization_id');
        $showOrgOverview = false; // Show organization cards for super admin
        
        if ($isSuperAdmin) {
            $orgsData = $api->organizations();
            $organizations = $orgsData['organizations'] ?? [];
            
            // Organization cards are removed - use header dropdown only
            // showOrgOverview always stays false
        }
        
        // Add organization filtering
        $orgId = $selectedOrgId ?: $this->getOrganizationId();
        if ($orgId) {
            $filters['organization_id'] = $orgId;
        }
        
        // Fetch records with filters (only if not in overview mode)
        $records = [];
        if (!$showOrgOverview) {
            $data = $api->attendanceList($filters);
            $records = $data['attendance'] ?? [];
            
            // Sorting
            $sortField = $request->input('sort', 'date');
            $sortDir = $request->input('dir', 'desc');
            $validSortFields = ['employee_name', 'organization_name', 'date', 'checkin_time', 'checkout_time', 'status', 'late_duration', 'device_id'];
            
            if (in_array($sortField, $validSortFields) && !empty($records)) {
                $records = collect($records)->sortBy(function ($rec) use ($sortField) {
                    $value = $rec[$sortField] ?? '';
                    // Handle late_duration sorting (format like "5:30" or "-")
                    if ($sortField === 'late_duration') {
                        if ($value === '-' || empty($value)) return 0;
                        $parts = explode(':', $value);
                        return (int) ($parts[0] ?? 0) * 60 + (int) ($parts[1] ?? 0);
                    }
                    // Handle date/time fields
                    if (in_array($sortField, ['date', 'checkin_time', 'checkout_time'])) {
                        return $value ?: '9999-99-99';
                    }
                    // String sorting (case-insensitive)
                    return strtolower((string) $value);
                }, SORT_REGULAR, $sortDir === 'desc')->values()->all();
            }
        }
        
        // Fetch employees for filter dropdowns (with org filtering)
        $empData = $api->employees($orgId);
        $employees = $empData['employees'] ?? [];
        
        $departments = collect($employees)->pluck('department')->filter(fn($v) => !empty($v))->unique()->values()->all();
        $designations = collect($employees)->pluck('designation')->filter(fn($v) => !empty($v))->unique()->values()->all();
        
        // Get selected org name for display
        $selectedOrgName = null;
        if ($selectedOrgId && !empty($organizations)) {
            foreach ($organizations as $org) {
                if ($org['id'] == $selectedOrgId) {
                    $selectedOrgName = $org['name'];
                    break;
                }
            }
        }
        
        return view('attendance_records.index', compact(
            'records', 'departments', 'designations', 
            'isSuperAdmin', 'userRole', 'organizations',
            'showOrgOverview', 'selectedOrgId', 'selectedOrgName'
        ));
    }

    public function create(DjangoApi $api)
    {
        // Fetch employees and shifts for the dropdown (with org filtering)
        $orgId = $this->getOrganizationId();
        $employeesData = $api->employees($orgId);
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
        $checkinFile = $request->file('checkin_image');
        if ($checkinFile && $checkinFile->isValid() && $checkinFile->getSize() > 0) {
            $files['checkin_image'] = $checkinFile;
        }
        $checkoutFile = $request->file('checkout_image');
        if ($checkoutFile && $checkoutFile->isValid() && $checkoutFile->getSize() > 0) {
            $files['checkout_image'] = $checkoutFile;
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
        $orgId = $this->getOrganizationId();
        $employeesData = $api->employees($orgId);
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
        
        // Debug: Log the employee_id being sent
        \Log::info('Attendance update payload: ', [
            'employee_id' => $payload['employee_id'] ?? 'MISSING',
            'date' => $payload['date'] ?? 'MISSING',
            'all_payload' => $payload
        ]);
        
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
        $checkinFile = $request->file('checkin_image');
        $checkoutFile = $request->file('checkout_image');
        
        \Log::info("=== FILE DEBUG ===", [
            'checkin_exists' => $checkinFile ? 'yes' : 'no',
            'checkin_valid' => $checkinFile ? ($checkinFile->isValid() ? 'yes' : 'no') : 'n/a',
            'checkin_size' => $checkinFile ? $checkinFile->getSize() : 0,
            'checkout_exists' => $checkoutFile ? 'yes' : 'no',
            'checkout_valid' => $checkoutFile ? ($checkoutFile->isValid() ? 'yes' : 'no') : 'n/a',
            'checkout_size' => $checkoutFile ? $checkoutFile->getSize() : 0,
        ]);
        
        if ($checkinFile && $checkinFile->isValid() && $checkinFile->getSize() > 0) {
            $files['checkin_image'] = $checkinFile;
        }
        if ($checkoutFile && $checkoutFile->isValid() && $checkoutFile->getSize() > 0) {
            $files['checkout_image'] = $checkoutFile;
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
        \Log::info("DELETE ATTENDANCE: Attempting to delete record ID: {$id}");
        $resp = $api->deleteAttendance($id);
        \Log::info("DELETE ATTENDANCE: API Response: " . json_encode($resp));
        
        if (!empty($resp['error'])) {
            \Log::error("DELETE ATTENDANCE: Error: " . $resp['error']);
            return redirect()->back()->with('error', 'Delete failed: ' . $resp['error']);
        }
        return redirect()->back()->with('success', 'Record deleted successfully');
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
