<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) { $table->boolean('must_change_password')->default(false)->after('is_super_admin'); });
        Schema::table('tenants', function (Blueprint $table) { $table->string('address')->nullable()->after('phone'); });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'action']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('audit_logs');
        Schema::table('tenants', function (Blueprint $table) { $table->dropColumn('address'); });
        Schema::table('users', function (Blueprint $table) { $table->dropColumn('must_change_password'); });
    }
};
