<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Intake extends Model {
    protected $guarded = ['id'];
    protected $casts = ['received_at' => 'datetime'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by'); }
    public function repairJobs() { return $this->hasMany(RepairJob::class); }
}