<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model {
    protected $guarded = ['id'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function intake() { return $this->belongsTo(Intake::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function finalizedBy() { return $this->belongsTo(User::class, 'finalized_by'); }
    public function payments() { return $this->hasMany(Payment::class); }
}