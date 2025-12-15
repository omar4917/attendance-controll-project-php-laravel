<?php

namespace App\Traits;

use Illuminate\Support\Facades\Session;

/**
 * Trait for getting organization context in controllers.
 * Used for multi-tenant data filtering.
 */
trait HasOrganizationContext
{
    /**
     * Get the organization ID that should be used for filtering data.
     * 
     * For super_admin:
     *   - Returns selected_organization_id if they've selected a specific org
     *   - Returns null if "All Organizations" is selected (no filtering)
     * 
     * For org_admin/org_viewer:
     *   - Returns their assigned organization_id (always filtered)
     * 
     * @return int|null
     */
    protected function getOrganizationId(): ?int
    {
        $role = Session::get('user_role', 'org_admin');
        
        if ($role === 'super_admin') {
            // Super admins can view all or select specific org
            $selectedOrgId = Session::get('selected_organization_id');
            return $selectedOrgId ? (int) $selectedOrgId : null;
        }
        
        // Org admins/viewers are always restricted to their org
        $orgId = Session::get('organization_id');
        return $orgId ? (int) $orgId : null;
    }

    /**
     * Check if current user is super admin
     */
    protected function isSuperAdmin(): bool
    {
        return Session::get('user_role') === 'super_admin';
    }

    /**
     * Check if current user can access all organizations
     */
    protected function canAccessAllOrganizations(): bool
    {
        return $this->isSuperAdmin() && !Session::get('selected_organization_id');
    }

    /**
     * Get organization name for display
     */
    protected function getOrganizationName(): string
    {
        if ($this->isSuperAdmin()) {
            return Session::get('selected_organization_name', 'All Organizations');
        }
        return Session::get('organization_name', 'Unknown');
    }
}
