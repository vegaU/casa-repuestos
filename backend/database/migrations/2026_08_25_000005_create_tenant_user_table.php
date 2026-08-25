<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Link users to companies and define their access role. */
    public function up(): void
    {
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50)->default('staff');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->primary(['tenant_id', 'user_id']);
        });
    }

    /** Remove the user-to-company links. */
    public function down(): void
    {
        Schema::dropIfExists('tenant_user');
    }
};
