<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $data = $api->holidays($orgId);
        $holidays = $data['holidays'] ?? ($data['data'] ?? []);
        $error = $data['error'] ?? null;
        
        // Sorting
        $sortField = $request->input('sort', 'start_date');
        $sortDir = $request->input('dir', 'asc');
        $validSortFields = ['name', 'start_date', 'end_date', 'is_active', 'is_government', 'created_at'];
        
        if (in_array($sortField, $validSortFields) && !empty($holidays)) {
            $holidays = collect($holidays)->sortBy(function ($h) use ($sortField) {
                $value = $h[$sortField] ?? '';
                if (in_array($sortField, ['is_active', 'is_government'])) {
                    return $value ? 1 : 0;
                }
                return strtolower((string) $value);
            }, SORT_REGULAR, $sortDir === 'desc')->values()->all();
        }
        
        return view('holidays.index', compact('holidays', 'error'));
    }

    public function edit($id, DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $data = $api->holidays($orgId);
        $holidays = $data['holidays'] ?? ($data['data'] ?? []);
        $holiday = null;
        foreach ($holidays as $h) {
            if (($h['id'] ?? '') == $id) {
                $holiday = $h;
                break;
            }
        }
        if (!$holiday) {
            return redirect()->route('holidays.index')->with('error', 'Holiday not found');
        }
        return view('holidays.edit', compact('holiday'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only(['id','name','start_date','end_date','scope','is_active','is_government']);
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        $resp = $api->upsertHoliday($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('holidays.index')->with('success', 'Holiday saved');
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->only(['name','start_date','end_date','scope','is_active','is_government']);
        $payload['id'] = $id;
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        $resp = $api->upsertHoliday($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('holidays.index')->with('success', 'Holiday updated');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteHoliday($id);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->route('holidays.index')->with('success', 'Holiday deleted');
    }

    public function generate(Request $request, DjangoApi $api)
    {
        $year = $request->input('year', date('Y'));
        $orgId = $this->getOrganizationId();
        $resp = $api->generateBulkHolidays($year, $orgId);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        $count = $resp['count'] ?? 0;
        
        // Log the generation action
        try {
            $api->logAction([
                'action' => 'create',
                'resource_type' => 'holiday',
                'organization_id' => $orgId,
                'user_email' => \Session::get('admin_email', \Session::get('admin_user', 'unknown')),
                'user_name' => \Session::get('admin_name', \Session::get('admin_user', 'unknown')),
                'details' => [
                    'type' => 'bulk_generation',
                    'year' => $year,
                    'count' => $count,
                    'source' => 'php_holiday_generator'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log holiday generation: ' . $e->getMessage());
        }

        return redirect()->route('holidays.index')->with('success', "Generated {$count} government holidays for {$year}");
    }
}
