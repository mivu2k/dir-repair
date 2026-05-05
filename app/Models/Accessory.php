<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Accessory extends Model {
    protected $guarded = ['id'];
    public function repairJobs() { return $this->belongsToMany(RepairJob::class, 'job_accessories'); }
}