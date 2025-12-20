<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    use HasOrganizationContext;

    public function index(DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $data = $api->salaryStatistics($orgId);
        $stats = $data['statistics'] ?? ($data['data'] ?? []);
        $error = $data['error'] ?? null;
        return view('salary.index', compact('stats', 'error'));
    }

    public function edit($id, DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        // Fetch single salary record
        $data = $api->salaryStatistics($orgId);
        $stats = $data['statistics'] ?? ($data['data'] ?? []);
        $salary = collect($stats)->firstWhere('id', $id);
        
        if (!$salary) {
            return redirect()->route('salary.index')->with('error', 'Salary record not found');
        }

        // Fetch employees for dropdown (with org filtering)
        $employeesData = $api->employees($orgId);
        $employees = $employeesData['employees'] ?? [];
        
        $error = $data['error'] ?? null;
        return view('salary.edit', compact('salary', 'employees', 'error'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only(['id','employee_id','month','year','gross_salary','payable']);
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
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
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
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
        $orgId = $this->getOrganizationId();
        $data = $api->salaryDefaults($orgId);
        $defaults = $data['defaults'] ?? [];
        $error = $data['error'] ?? null;
        return view('salary.defaults', compact('defaults', 'error'));
    }

    public function saveDefaults(Request $request, DjangoApi $api)
    {
        $payload = $request->except('_token');
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        $resp = $api->saveSalaryDefaults($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('salary.defaults')->with('success', 'Defaults saved');
    }
}
