<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairJob extends Model {
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = ['expected_delivery_date' => 'date', 'status_updated_at' => 'datetime', 'delivered_at' => 'datetime'];
    public function getRouteKeyName() { return 'job_number'; }
    public function intake() { return $this->belongsTo(Intake::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    // Canonical relationship using actual DB column
    public function assignedTechnician() { return $this->belongsTo(User::class, 'assigned_technician_id'); }
    // Alias used in controllers & views
    public function technician() { return $this->belongsTo(User::class, 'assigned_technician_id'); }
    // Both singular and plural for statusHistories
    public function statusHistory() { return $this->hasMany(JobStatusHistory::class); }
    public function statusHistories() { return $this->hasMany(JobStatusHistory::class); }
    public function symptoms() { return $this->belongsToMany(Symptom::class, 'job_symptoms'); }
    public function accessories() { return $this->belongsToMany(Accessory::class, 'job_accessories')->withPivot('note'); }
    // Singular and plural diagnosis
    public function diagnosis() { return $this->hasOne(Diagnosis::class); }
    public function diagnoses() { return $this->hasMany(Diagnosis::class); }
    public function quotation() { return $this->hasOne(Quotation::class); }
    public function approvedQuotation() { return $this->hasOne(Quotation::class)->where('status', 'approved'); }
    public function salesOrder() { return $this->hasOne(SalesOrder::class); }
    public function photos() { return $this->hasMany(JobPhoto::class); }
}