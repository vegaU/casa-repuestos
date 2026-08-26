<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model {
    protected $fillable=['actor_id','tenant_id','action','metadata','ip_address'];
    protected function casts(): array { return ['metadata'=>'array']; }
}
