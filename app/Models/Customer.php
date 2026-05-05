<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model {
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    public function intakes() { return $this->hasMany(Intake::class); }
    public function repairJobs() { return $this->hasMany(RepairJob::class); }
}