<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model {
    protected $guarded = ['id'];
    
    public function quotation() { 
        return $this->belongsTo(Quotation::class); 
    }
    
    public function repairJob() { 
        return $this->belongsTo(RepairJob::class); 
    }

    public function part() {
        return $this->belongsTo(Part::class);
    }
}