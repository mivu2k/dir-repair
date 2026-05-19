<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function checkDeletePermission(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin', 'manager'])) {
            abort(403, 'Only Admins and Managers can delete records.');
        }
    }

    protected function checkEditPermission(Request $request, $model, $statusField = 'status', $approvedStatuses = ['approved', 'completed', 'delivered', 'cancelled', 'returned'])
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }
        
        // Admin and Manager can always edit/update
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return;
        }

        // Standard user: Check if model has a finalized/approved status
        $status = strtolower($model->{$statusField} ?? '');
        if (in_array($status, $approvedStatuses)) {
            abort(403, 'This record has been approved/finalized and cannot be edited by standard users.');
        }
    }
}
