<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $data = $api->shifts($orgId);
        $shifts = $data['shifts'] ?? ($data['data'] ?? []);
        $error = $data['error'] ?? null;
        
        // Sorting
        $sortField = $request->input('sort', 'name');
        $sortDir = $request->input('dir', 'asc');
        $validSortFields = ['name', 'start', 'allowed_late_minutes', 'is_active'];
        
        if (in_array($sortField, $validSortFields) && !empty($shifts)) {
            $shifts = collect($shifts)->sortBy(function ($s) use ($sortField) {
                $value = $s[$sortField] ?? '';
                if ($sortField === 'allowed_late_minutes') {
                    return (int) $value;
                }
                if ($sortField === 'is_active') {
                    return $value ? 1 : 0;
                }
                return strtolower((string) $value);
            }, SORT_REGULAR, $sortDir === 'desc')->values()->all();
        }
        
        return view('shifts.index', compact('shifts', 'error'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->except('_token');
        // Handle checkbox
        if (!isset($payload['is_active'])) {
            $payload['is_active'] = false;
        }
        // Add organization_id
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        
        $resp = $api->saveShift($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('shifts.index')->with('success', 'Shift saved');
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->except(['_token', '_method']);
        $payload['id'] = $id;
        // Handle checkbox
        if (!isset($payload['is_active'])) {
            $payload['is_active'] = false;
        }
        // Add organization_id
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        
        $resp = $api->saveShift($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('shifts.index')->with('success', 'Shift updated');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteShift($id);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->route('shifts.index')->with('success', 'Shift deleted');
    }
}
