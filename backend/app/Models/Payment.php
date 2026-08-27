<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\MorphTo;
class Payment extends Model { protected $fillable=['tenant_id','payable_type','payable_id','amount','tendered_amount','change_amount','method','reference','paid_at','created_by']; protected function casts(): array{return ['amount'=>'decimal:2','tendered_amount'=>'decimal:2','change_amount'=>'decimal:2','paid_at'=>'datetime'];} public function payable():MorphTo{return $this->morphTo();} }
