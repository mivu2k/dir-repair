<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoIssuance extends Model
{
    protected $fillable = [
        'issuance_number', 'customer_id', 'items',
        'issued_at', 'expected_return_date', 
        'returned_at', 'status', 'notes', 'issued_by', 'received_by'
    ];
    
    protected $casts = [
        'items' => 'array',
        'issued_at' => 'datetime',
        'expected_return_date' => 'date',
        'returned_at' => 'datetime',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
    public function issuedBy() {
        return $this->belongsTo(User::class, 'issued_by');
    }
    public function receivedBy() {
        return $this->belongsTo(User::class, 'received_by');
    }
}
