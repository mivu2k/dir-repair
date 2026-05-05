<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JobPhoto extends Model {
    protected $guarded = ['id'];
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by'); }
}