<?php
$models = [
    'User' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable {
    use HasFactory, Notifiable, HasRoles, SoftDeletes;
    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'avatar'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean']; }
}
EOT,
    'Customer' => <<<'EOT'
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
EOT,
    'Intake' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Intake extends Model {
    protected $guarded = ['id'];
    protected $casts = ['received_at' => 'datetime'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by'); }
    public function repairJobs() { return $this->hasMany(RepairJob::class); }
}
EOT,
    'RepairJob' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairJob extends Model {
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = ['expected_delivery_date' => 'date', 'status_updated_at' => 'datetime', 'delivered_at' => 'datetime'];
    public function intake() { return $this->belongsTo(Intake::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function assignedTechnician() { return $this->belongsTo(User::class, 'assigned_technician_id'); }
    public function statusHistory() { return $this->hasMany(JobStatusHistory::class); }
    public function symptoms() { return $this->belongsToMany(Symptom::class, 'job_symptoms'); }
    public function accessories() { return $this->belongsToMany(Accessory::class, 'job_accessories')->withPivot('note'); }
    public function diagnosis() { return $this->hasOne(Diagnosis::class); }
    public function quotation() { return $this->hasOne(Quotation::class); }
    public function photos() { return $this->hasMany(JobPhoto::class); }
}
EOT,
    'JobStatusHistory' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JobStatusHistory extends Model {
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime'];
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
}
EOT,
    'Symptom' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Symptom extends Model {
    protected $guarded = ['id'];
    public function repairJobs() { return $this->belongsToMany(RepairJob::class, 'job_symptoms'); }
}
EOT,
    'Accessory' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Accessory extends Model {
    protected $guarded = ['id'];
    public function repairJobs() { return $this->belongsToMany(RepairJob::class, 'job_accessories'); }
}
EOT,
    'Diagnosis' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model {
    protected $guarded = ['id'];
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
}
EOT,
    'Quotation' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model {
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = ['customer_approved_at' => 'datetime', 'manager_approved_at' => 'datetime', 'valid_until' => 'date'];
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function preparedBy() { return $this->belongsTo(User::class, 'prepared_by'); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function items() { return $this->hasMany(QuotationItem::class); }
    public function salesOrder() { return $this->hasOne(SalesOrder::class); }
}
EOT,
    'QuotationItem' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model {
    protected $guarded = ['id'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
}
EOT,
    'SalesOrder' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model {
    protected $guarded = ['id'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function finalizedBy() { return $this->belongsTo(User::class, 'finalized_by'); }
}
EOT,
    'JobPhoto' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JobPhoto extends Model {
    protected $guarded = ['id'];
    public function repairJob() { return $this->belongsTo(RepairJob::class); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
EOT,
    'Sequence' => <<<'EOT'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model {
    protected $guarded = ['id'];
    public $timestamps = false;
}
EOT,
];

foreach ($models as $name => $content) {
    file_put_contents(__DIR__ . "/app/Models/{$name}.php", $content);
}
echo "Models updated successfully.\n";
