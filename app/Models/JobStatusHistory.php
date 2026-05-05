<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JobStatusHistory extends Model {
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime'];
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
    // Alias used in views
    public function changer() { return $this->belongsTo(User::class, 'changed_by'); }
}