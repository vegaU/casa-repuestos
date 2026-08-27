<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = ['tenant_id', 'branch_id', 'supplier_id', 'purchase_number', 'supplier_document_number', 'status', 'subtotal', 'tax_total', 'total', 'purchased_at', 'created_by', 'notes'];
    protected function casts(): array { return ['subtotal' => 'decimal:2', 'tax_total' => 'decimal:2', 'total' => 'decimal:2', 'purchased_at' => 'datetime']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseItem::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
