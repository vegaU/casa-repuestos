<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['tenant_id', 'name', 'legal_name', 'tax_id', 'email', 'phone', 'address', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
}
