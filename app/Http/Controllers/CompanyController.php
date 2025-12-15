<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of organizations (companies).
     * Super Admin only.
     */
    public function index(Request $request, DjangoApi $api)
    {
        $query = [];
        
        // Search filter
        if ($request->has('search') && $request->input('search')) {
            $query['search'] = $request->input('search');
        }
        
        // Active filter
        if ($request->has('active') && $request->input('active') !== '') {
            $query['is_active'] = $request->input('active') === 'yes' ? 'true' : 'false';
        }
        
        $data = $api->organizations($query);
        $organizations = $data['organizations'] ?? [];
        $error = $data['error'] ?? null;
        
        return view('companies.index', compact('organizations', 'error'));
    }

    /**
     * Show the form for creating a new organization.
     */
    public function create()
    {
        return view('companies.form');
    }

    /**
     * Store a newly created organization.
     */
    public function store(Request $request, DjangoApi $api)
    {
        $payload = [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'max_employees' => $request->input('max_employees', 100),
            'max_devices' => $request->input('max_devices', 5),
            'is_active' => $request->has('is_active'),
        ];
        
        $resp = $api->createOrganization($payload);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        return redirect()->route('companies.index')->with('success', 'Organization created successfully');
    }

    /**
     * Display the specified organization with its devices.
     */
    public function show($id, DjangoApi $api)
    {
        $orgData = $api->organization($id);
        
        if (!empty($orgData['error'])) {
            return redirect()->route('companies.index')->with('error', 'Organization not found');
        }
        
        $organization = $orgData['organization'] ?? null;
        
        if (!$organization) {
            return redirect()->route('companies.index')->with('error', 'Organization not found');
        }
        
        // Get stats
        $statsData = $api->organizationStats($id);
        $stats = $statsData['stats'] ?? [];
        
        // Get devices
        $devicesData = $api->organizationDevices($id);
        $devices = $devicesData['devices'] ?? [];
        
        return view('companies.show', compact('organization', 'stats', 'devices'));
    }

    /**
     * Show the form for editing the specified organization.
     */
    public function edit($id, DjangoApi $api)
    {
        $orgData = $api->organization($id);
        $organization = $orgData['organization'] ?? null;
        
        if (!$organization) {
            return redirect()->route('companies.index')->with('error', 'Organization not found');
        }
        
        return view('companies.form', compact('organization'));
    }

    /**
     * Update the specified organization.
     */
    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'max_employees' => $request->input('max_employees', 100),
            'max_devices' => $request->input('max_devices', 5),
            'is_active' => $request->has('is_active'),
        ];
        
        $resp = $api->updateOrganization($id, $payload);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        return redirect()->route('companies.index')->with('success', 'Organization updated successfully');
    }

    /**
     * Remove the specified organization.
     */
    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteOrganization($id);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        
        return redirect()->route('companies.index')->with('success', 'Organization deleted');
    }

    /**
     * Show devices for an organization.
     */
    public function devices($id, DjangoApi $api)
    {
        $orgData = $api->organization($id);
        $organization = $orgData['organization'] ?? null;
        
        if (!$organization) {
            return redirect()->route('companies.index')->with('error', 'Organization not found');
        }
        
        $devicesData = $api->organizationDevices($id);
        $devices = $devicesData['devices'] ?? [];
        
        return view('companies.devices', compact('organization', 'devices'));
    }

    /**
     * Store a new device for an organization.
     */
    public function storeDevice(Request $request, $orgId, DjangoApi $api)
    {
        $payload = [
            'organization_id' => $orgId,
            'device_id' => $request->input('device_id'),
            'device_name' => $request->input('device_name'),
            'location' => $request->input('location'),
            'is_active' => $request->has('is_active'),
        ];
        
        $resp = $api->createDevice($payload);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        return redirect()->route('companies.devices', $orgId)->with('success', 'Device added successfully');
    }

    /**
     * Update a device.
     */
    public function updateDevice(Request $request, $orgId, $deviceId, DjangoApi $api)
    {
        $payload = [
            'device_name' => $request->input('device_name'),
            'location' => $request->input('location'),
            'is_active' => $request->has('is_active'),
        ];
        
        $resp = $api->updateDevice($deviceId, $payload);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        
        return redirect()->route('companies.devices', $orgId)->with('success', 'Device updated');
    }

    /**
     * Delete a device.
     */
    public function destroyDevice($orgId, $deviceId, DjangoApi $api)
    {
        $resp = $api->deleteDevice($deviceId);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        
        return redirect()->route('companies.devices', $orgId)->with('success', 'Device deleted');
    }
}
