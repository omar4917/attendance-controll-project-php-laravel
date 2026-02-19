<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $data = $api->salaryStatistics($orgId, $month, $year);
        $stats = $data['statistics'] ?? ($data['data'] ?? []);
        $error = $data['error'] ?? null;
        return view('salary.index', compact('stats', 'error', 'month', 'year'));
    }

    public function generate(Request $request, DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $payload = [
            'month' => $month,
            'year' => $year,
        ];

        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }

        $resp = $api->generateSalaryStatistics($payload);

        if (!empty($resp['error'])) {
            return redirect()->route('salary.index', ['month' => $month, 'year' => $year])
                ->with('error', $resp['error']);
        }

        return redirect()->route('salary.index', ['month' => $month, 'year' => $year])
            ->with('success', $resp['message'] ?? 'Salary reports generated successfully');
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
        $payload = $request->except(['_token', '_method', 'continue_editing']);
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        $resp = $api->upsertSalaryStatistic($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }

        if ($request->has('continue_editing')) {
            $newId = $resp['id'] ?? $payload['id'] ?? null;
            if ($newId) {
                return redirect()->route('salary.edit', $newId)->with('success', 'Salary statistic saved');
            }
        }

        return redirect()->route('salary.index')->with('success', 'Salary statistic saved');
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->except(['_token', '_method', 'continue_editing']);
        $payload['id'] = $id;
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        $resp = $api->upsertSalaryStatistic($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }

        if ($request->has('continue_editing')) {
            return redirect()->route('salary.edit', $id)->with('success', 'Salary statistic updated');
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
        \Log::info("SalaryController: saveDefaults POST started", ['payload' => $request->except('_token')]);
        $payload = $request->except('_token');
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }

        try {
            \Log::info("SalaryController: Calling DjangoApi->saveSalaryDefaults");
            $resp = $api->saveSalaryDefaults($payload);
            \Log::info("SalaryController: DjangoApi Response", ['response' => $resp]);

            if (!empty($resp['error'])) {
                \Log::error("SalaryController: API Error detected", ['error' => $resp['error']]);
                return redirect()->back()->withInput()->with('error', $resp['error']);
            }

            return redirect()->route('salary.defaults')->with('success', 'Defaults saved');
        } catch (\Exception $e) {
            \Log::error("SalaryController: Exception during saveDefaults", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
}
