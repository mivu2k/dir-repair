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
        if (!$request->user()) {
            return redirect('login');
        }

        $userRole = $request->user()->role;
        
        // Admin bypass
        if ($userRole === 'admin') {
            return $next($request);
        }

        // General whitelist check: if roles are specified, the user must have one of them
        if (!empty($roles) && !in_array($userRole, $roles)) {
            abort(403, 'You do not have permission to access this module.');
        }

        // Specific exclusions for technicians
        if ($userRole === 'technician') {
            // Can access Intakes but not modify/delete them
            if ($request->routeIs('intakes.update', 'intakes.destroy', 'intakes.edit')) {
                abort(403, 'Technicians cannot modify or delete intake batches.');
            }
            
            // Can access Repair Jobs but not delete them
            if ($request->routeIs('jobs.destroy')) {
                abort(403, 'Technicians cannot delete repair units.');
            }
        }

        return $next($request);
    }
}
