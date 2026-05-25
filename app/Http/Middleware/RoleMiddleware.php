<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect('login');
        }

        // Admin bypass
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        // Route-to-Permission Mapping based on standard resource/custom naming
        $routePermissionMap = [
            // Customers
            'customers.index' => 'view customers',
            'customers.show' => 'view customers',
            'customers.create' => 'create customers',
            'customers.store' => 'create customers',
            'customers.edit' => 'edit customers',
            'customers.update' => 'edit customers',
            'customers.destroy' => 'delete customers',

            // Intakes
            'intakes.index' => 'view intakes',
            'intakes.show' => 'view intakes',
            'intakes.pdf' => 'view intakes',
            'intakes.stickers' => 'view intakes',
            'intakes.create' => 'create intakes',
            'intakes.store' => 'create intakes',
            'intakes.edit' => 'edit intakes',
            'intakes.update' => 'edit intakes',
            'intakes.status.update' => 'edit intakes',
            'intakes.destroy' => 'delete intakes',

            // Jobs
            'jobs.index' => 'view jobs',
            'jobs.show' => 'view jobs',
            'jobs.pdf' => 'view jobs',
            'jobs.pos' => 'view jobs',
            'jobs.sticker' => 'view jobs',
            'jobs.edit' => 'edit jobs',
            'jobs.update' => 'edit jobs',
            'jobs.assign' => 'edit jobs',
            'jobs.destroy' => 'delete jobs',
            'jobs.status' => 'update job status',
            'jobs.diagnose' => 'add diagnosis',
            'diagnoses.update' => 'add diagnosis',
            'diagnoses.destroy' => 'add diagnosis',

            // Parts / Inventory
            'parts.index' => 'view parts',
            'parts.show' => 'view parts',
            'parts.create' => 'create parts',
            'parts.store' => 'create parts',
            'parts.edit' => 'edit parts',
            'parts.update' => 'edit parts',
            'parts.destroy' => 'delete parts',

            // Demo Issuances
            'demo-issuances.index' => 'view demo-issuances',
            'demo-issuances.show' => 'view demo-issuances',
            'demo-issuances.pdf' => 'view demo-issuances',
            'demo-issuances.create' => 'create demo-issuances',
            'demo-issuances.store' => 'create demo-issuances',
            'demo-issuances.edit' => 'edit demo-issuances',
            'demo-issuances.update' => 'edit demo-issuances',
            'demo-issuances.return' => 'edit demo-issuances',
            'demo-issuances.destroy' => 'delete demo-issuances',

            // Gate Passes
            'gate-passes.index' => 'view gate-passes',
            'gate-passes.show' => 'view gate-passes',
            'gate-passes.pdf' => 'view gate-passes',
            'gate-passes.create' => 'create gate-passes',
            'gate-passes.store' => 'create gate-passes',
            'gate-passes.edit' => 'edit gate-passes',
            'gate-passes.update' => 'edit gate-passes',
            'gate-passes.destroy' => 'delete gate-passes',

            // Quotations
            'quotations.index' => 'view quotations',
            'quotations.show' => 'view quotations',
            'quotations.pdf' => 'view quotations',
            'quotations.create' => 'create quotations',
            'quotations.store' => 'create quotations',
            'quotations.edit' => 'edit quotations',
            'quotations.update' => 'edit quotations',
            'quotations.status' => 'approve quotation',
            'quotations.destroy' => 'delete quotations',

            // Financial & Reports & Sales Orders
            'tracking.index' => 'view financial data',
            'tracking.pdf' => 'view financial data',
            'tracking.csv' => 'view financial data',
            'reports.index' => 'view reports',
            'reports.logistics' => 'view reports',
            'reports.pdf' => 'view reports',
            'reports.excel' => 'view reports',
            'sales-orders.index' => 'view sales-orders',
            'sales-orders.show' => 'view sales-orders',
            'sales-orders.pdf' => 'view sales-orders',
            'sales-orders.store' => 'create sales-orders',
            'sales-orders.payments.store' => 'edit sales-orders',
        ];

        // 1. Dynamic permission check
        if ($routeName && isset($routePermissionMap[$routeName])) {
            $requiredPermission = $routePermissionMap[$routeName];
            if (!$user->hasPermissionTo($requiredPermission)) {
                abort(403, 'You do not have permission to access this operation.');
            }
            return $next($request);
        }

        // 2. Fallback to original static role whitelisting
        $userRole = $user->role;
        if (!empty($roles) && !in_array($userRole, $roles)) {
            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
