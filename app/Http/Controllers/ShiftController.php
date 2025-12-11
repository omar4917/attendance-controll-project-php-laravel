<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(DjangoApi $api)
    {
        $data = $api->shifts();
        $shifts = $data['shifts'] ?? ($data['data'] ?? []);
        $error = $data['error'] ?? null;
        return view('shifts.index', compact('shifts', 'error'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->except('_token');
        // Handle checkbox
        if (!isset($payload['is_active'])) {
            $payload['is_active'] = false;
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
