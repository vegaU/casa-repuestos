<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    protected $fillable = ['tenant_id', 'branch_id', 'customer_id', 'sale_number', 'status', 'subtotal', 'discount_total', 'tax_total', 'total', 'sold_at', 'created_by', 'notes'];
    protected function casts(): array { return ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'tax_total' => 'decimal:2', 'total' => 'decimal:2', 'sold_at' => 'datetime']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(SaleItem::class); }
    public function payments(): MorphMany { return $this->morphMany(Payment::class, 'payable'); }

    public function getPaidAmountAttribute(): string { return (string) $this->payments()->sum('amount'); }
    public function getBalanceAttribute(): string { return bcsub((string) $this->total, (string) $this->paid_amount, 2); }
}
