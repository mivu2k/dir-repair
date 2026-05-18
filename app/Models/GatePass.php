<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatePass extends Model
{
    protected $fillable = [
        'pass_number', 'type', 'person_name', 'company_name', 
        'vehicle_number', 'items', 'status', 'notes', 
        'authorized_by', 'reference_id', 'reference_type'
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function getItemsAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function authorizedBy() {
        return $this->belongsTo(User::class, 'authorized_by');
    }
    
    public function reference() {
        return $this->morphTo();
    }
}
