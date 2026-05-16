<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatePass extends Model
{
    protected $fillable = [
        'pass_number', 'type', 'person_name', 'company_name', 'vehicle_number',
        'items_description', 'status', 'authorized_by', 'notes',
        'reference_type', 'reference_id'
    ];

    public function authorizedBy() {
        return $this->belongsTo(User::class, 'authorized_by');
    }
    
    public function reference() {
        return $this->morphTo();
    }
}
