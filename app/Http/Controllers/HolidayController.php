<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(DjangoApi $api)
    {
        $data = $api->holidays();
        $holidays = $data['holidays'] ?? ($data['data'] ?? []);
        $error = $data['error'] ?? null;
        return view('holidays.index', compact('holidays', 'error'));
    }

    public function edit($id, DjangoApi $api)
    {
        $data = $api->holidays();
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
        $payload = $request->only(['id','name','start_date','end_date','scope','is_active']);
        $resp = $api->upsertHoliday($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('holidays.index')->with('success', 'Holiday saved');
    }
    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->only(['name','start_date','end_date','scope','is_active']);
        $payload['id'] = $id;
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
        $resp = $api->generateBulkHolidays($year);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        $count = $resp['count'] ?? 0;
        return redirect()->route('holidays.index')->with('success', "Generated {$count} government holidays for {$year}");
    }
}
