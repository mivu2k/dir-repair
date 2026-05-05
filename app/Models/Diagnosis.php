<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $guarded = ['id'];

    public function repairJob()
    {
        return $this->belongsTo(RepairJob::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}