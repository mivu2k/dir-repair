<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model {
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = ['customer_approved_at' => 'datetime', 'manager_approved_at' => 'datetime', 'valid_until' => 'date', 'date' => 'date'];
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function intake() { return $this->belongsTo(Intake::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function preparedBy() { return $this->belongsTo(User::class, 'prepared_by'); }
    // Alias for controllers that use createdBy
    public function createdBy() { return $this->belongsTo(User::class, 'prepared_by'); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function items() { return $this->hasMany(QuotationItem::class); }
    public function salesOrder() { return $this->hasOne(SalesOrder::class); }
}