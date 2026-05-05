<?php

namespace App\Services;

use App\Models\RepairJob;
use App\Models\JobStatusHistory;
use Illuminate\Support\Facades\Auth;

class JobStatusService
{
    public static function changeStatus(RepairJob $job, string $newStatus, ?string $note = null): bool
    {
        $oldStatus = $job->status;
        
        if ($oldStatus === $newStatus) {
            return false;
        }
        
        $job->update([
            'status' => $newStatus,
            'status_updated_at' => now(),
            'delivered_at' => $newStatus === 'delivered' ? now() : $job->delivered_at,
        ]);

        JobStatusHistory::create([
            'repair_job_id' => $job->id,
            'changed_by' => Auth::id() ?? 1, // Fallback to 1 for automated transitions if not auth
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'note' => $note,
            'created_at' => now(),
        ]);

        return true;
    }
}
