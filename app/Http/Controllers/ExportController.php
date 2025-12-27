<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class ExportController extends Controller
{
    use HasOrganizationContext;

    /**
     * Display export/import page.
     */
    public function index(DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $isSuperAdmin = Session::get('is_superuser', false);
        
        // For super admins, get list of all organizations
        $organizations = [];
        if ($isSuperAdmin) {
            $orgsData = $api->organizations();
            $organizations = $orgsData['organizations'] ?? [];
        }
        
        return view('export.index', [
            'organizationId' => $orgId,
            'isSuperAdmin' => $isSuperAdmin,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Export organization data as JSON download.
     * Super admins can export for all orgs, selected orgs, or single org.
     */
    public function export(Request $request, DjangoApi $api)
    {
        $isSuperAdmin = Session::get('is_superuser', false);
        $exportMode = $request->input('export_mode', 'single'); // single, all, selected
        $selectedOrgs = $request->input('selected_orgs', []);
        
        
        $include = $request->input('include', ['employees', 'attendance', 'shifts', 'holidays']);
        if (is_array($include)) {
            $include = implode(',', $include);
        }
        
        $month = $request->input('month');
        $year = $request->input('year');
        
        // Handle super admin batch export
        if ($isSuperAdmin && $exportMode !== 'single') {
            $orgsData = $api->organizations();
            $allOrgs = $orgsData['organizations'] ?? [];
            
            if ($exportMode === 'selected' && !empty($selectedOrgs)) {
                $orgsToExport = array_filter($allOrgs, fn($o) => in_array($o['id'], $selectedOrgs));
            } else {
                $orgsToExport = $allOrgs;
            }
            
            if (empty($orgsToExport)) {
                return redirect()->route('export.index')->with('error', 'No organizations to export');
            }
            
            // Create ZIP with all organization exports
            $zipPath = tempnam(sys_get_temp_dir(), 'export_') . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                return redirect()->route('export.index')->with('error', 'Failed to create ZIP file');
            }
            
            $exportCount = 0;
            foreach ($orgsToExport as $org) {
                $data = $api->exportData($org['id'], $include, $month, $year);
                
                if (!isset($data['error'])) {
                    $orgName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $org['name']);
                    $zip->addFromString(
                        "{$orgName}_export_" . date('Y-m-d') . ".json",
                        json_encode($data, JSON_PRETTY_PRINT)
                    );
                    $exportCount++;
                }
            }
            
            $zip->close();
            
            if ($exportCount === 0) {
                @unlink($zipPath);
                return redirect()->route('export.index')->with('error', 'No data exported');
            }
            
            $filename = 'batch_export_' . date('Y-m-d_His') . '.zip';
            
            // Log batch export
            try {
                $api->logAction([
                    'action' => 'export',
                    'resource_type' => 'organization_data_batch',
                    'organization_id' => null, // Super admin action
                    'user_email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
                    'user_name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
                    'details' => [
                        'scope' => 'batch',
                        'org_count' => $exportCount,
                        'includes' => $include,
                        'source' => 'global_export'
                    ]
                ]);
            } catch (\Exception $e) { }

            return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
        }
        
        // Single organization export
        $orgId = $request->input('organization_id') ?: $this->getOrganizationId();
        
        if (!$orgId) {
            return redirect()->route('export.index')->with('error', 'No organization selected');
        }
        
        // Check permission for non-super admin
        if (!$isSuperAdmin && $orgId != $this->getOrganizationId()) {
            return redirect()->route('export.index')->with('error', 'Access denied');
        }
        
        $data = $api->exportData($orgId, $include, $month, $year);
        
        // Prepare log details
        $logDetails = [
            'scope' => 'single',
            'includes' => $include,
            'month' => $month,
            'year' => $year,
            'source' => 'global_export'
        ];

        if (isset($data['error'])) {
            // Log failure
            $logDetails['status'] = 'failed';
            $logDetails['error'] = $data['error']; // Capture error message
             try {
                $api->logAction([
                    'action' => 'export',
                    'resource_type' => 'organization_data',
                    'resource_id' => $orgId,
                    'organization_id' => $orgId,
                    'user_email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
                    'user_name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
                    'details' => $logDetails
                ]);
            } catch (\Exception $e) {}

            return redirect()->route('export.index')->with('error', $data['error']);
        }
        
        // Log success
        $logDetails['status'] = 'success';
        try {
            $api->logAction([
                'action' => 'export',
                'resource_type' => 'organization_data',
                'resource_id' => $orgId,
                'organization_id' => $orgId,
                'user_email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
                'user_name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
                'details' => $logDetails
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log export: ' . $e->getMessage());
        }
        
        // Create JSON download
        $filename = 'export_' . ($data['organization']['name'] ?? 'data') . '_' . date('Y-m-d') . '.json';
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        
        return Response::make(json_encode($data, JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Handle import form submission.
     * Super admins can import to any organization.
     */
    public function import(Request $request, DjangoApi $api)
    {
        $isSuperAdmin = Session::get('is_superuser', false);
        $targetOrgId = $request->input('target_org_id');
        
        // Determine target organization
        if ($isSuperAdmin && $targetOrgId) {
            $orgId = $targetOrgId;
        } else {
            $orgId = $this->getOrganizationId();
        }
        
        if (!$orgId) {
            return redirect()->route('export.index')->with('error', 'No organization selected');
        }
        
        $file = $request->file('import_file');
        if (!$file || !$file->isValid()) {
            return redirect()->route('export.index')->with('error', 'Please select a valid JSON file');
        }
        
        try {
            $content = file_get_contents($file->getPathname());
            $data = json_decode($content, true);
            
            if (!$data || json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON file');
            }
            
            // Add organization_id
            $data['organization_id'] = $orgId;
            
            $result = $api->importData($data);
            
            if (isset($result['error'])) {
                return redirect()->route('export.index')->with('error', $result['error']);
            }
            
            $imported = $result['imported'] ?? [];
            
            // Log the import action
            try {
                $api->logAction([
                    'action' => 'import',
                    'resource_type' => 'organization_data',
                    'resource_id' => $orgId,
                    'organization_id' => $orgId,
                    'user_email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
                    'user_name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
                    'details' => [
                        'filename' => $file->getClientOriginalName(),
                        'counts' => [
                            'employees' => $imported['employees'] ?? 0,
                            'shifts' => $imported['shifts'] ?? 0,
                            'holidays' => $imported['holidays'] ?? 0
                        ],
                        'source' => 'global_import'
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to log import: ' . $e->getMessage());
            }

            $message = sprintf(
                'Import complete: %d employees, %d shifts, %d holidays',
                $imported['employees'] ?? 0,
                $imported['shifts'] ?? 0,
                $imported['holidays'] ?? 0
            );
            
            if (!empty($imported['errors'])) {
                $message .= '. Warnings: ' . implode('; ', array_slice($imported['errors'], 0, 3));
            }
            
            return redirect()->route('export.index')->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->route('export.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
