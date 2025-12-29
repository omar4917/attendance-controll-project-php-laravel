<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use HasOrganizationContext;

    /**
     * Display analytics dashboard.
     * Super admins see system-wide data; org admins see only their org.
     */
    public function index(DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $userRole = \Session::get('user_role', 'org_admin');
        $isSuperAdmin = in_array($userRole, ['super_admin', 'shadow_admin']);
        
        $data = $api->analytics($orgId);
        
        $summary = $data['summary'] ?? [];
        $attendance = $data['attendance_this_month'] ?? [];
        $activity = $data['activity_last_7_days'] ?? [];
        $livefeedToday = $data['livefeed_today'] ?? 0;
        $planUsage = $data['plan_usage'] ?? [];
        $period = $data['period'] ?? [];
        $error = $data['error'] ?? null;
        
        // For org admins, extract their single org's plan usage
        $orgPlanUsage = null;
        if (!$isSuperAdmin && !empty($planUsage)) {
            $orgPlanUsage = $planUsage[0] ?? null;
        }
        
        return view('analytics.index', compact(
            'summary', 'attendance', 'activity', 'livefeedToday', 
            'planUsage', 'period', 'error', 'orgId', 'userRole', 
            'isSuperAdmin', 'orgPlanUsage'
        ));
    }
}
