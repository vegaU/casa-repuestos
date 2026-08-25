<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('purchases', fn(Blueprint $t) => $t->text('cancellation_reason')->nullable()); Schema::table('sales', fn(Blueprint $t) => $t->text('cancellation_reason')->nullable()); } public function down(): void { Schema::table('purchases', fn(Blueprint $t) => $t->dropColumn('cancellation_reason')); Schema::table('sales', fn(Blueprint $t) => $t->dropColumn('cancellation_reason')); } };
