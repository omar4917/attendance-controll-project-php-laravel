<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, DjangoApi $api)
    {
        // Get filter parameters
        $filters = $request->only(['month', 'year', 'department']);
        if (!isset($filters['month'])) {
            $filters['month'] = date('n');
        }
        if (!isset($filters['year'])) {
            $filters['year'] = date('Y');
        }
        
        // Fetch salary report data (same as salaryReport method)
        $data = $api->salaryReportDetailed($filters);
        
        $reports = $data['salary_data'] ?? [];
        $totals = $data['totals'] ?? [];
        $defaults = $data['defaults'] ?? [];
        $departments = $data['departments'] ?? [];
        $months = $data['months'] ?? range(1, 12);
        $years = $data['years'] ?? [date('Y') - 1, date('Y'), date('Y') + 1];
        $month = $filters['month'];
        $year = $filters['year'];
        $monthName = $data['month_name'] ?? date('F Y');
        $error = $data['error'] ?? null;
        
        return view('reports.index', compact(
            'reports', 'totals', 'defaults', 'departments', 
            'months', 'years', 'month', 'year', 'monthName', 'error'
        ));
    }
    public function salaryReport(Request $request, DjangoApi $api)
    {
        $filters = $request->only(['month', 'year', 'department']);
        $data = $api->salaryReportDetailed($filters);
        
        $salaryData = $data['salary_data'] ?? [];
        $totals = $data['totals'] ?? [];
        $defaults = $data['defaults'] ?? [];
        $departments = $data['departments'] ?? [];
        $months = $data['months'] ?? range(1, 12);
        $years = $data['years'] ?? [date('Y')];
        $monthName = $data['month_name'] ?? date('F Y');
        $error = $data['error'] ?? null;

        return view('reports.salary', compact(
            'salaryData', 'totals', 'defaults', 'departments', 
            'months', 'years', 'monthName', 'error'
        ));
    }
}
