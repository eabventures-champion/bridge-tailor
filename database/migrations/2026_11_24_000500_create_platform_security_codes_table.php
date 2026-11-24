<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_security_codes', function (Blueprint $table) {
            $table->id();
            $table->string('access_code', 64)->unique()->index();
            $table->string('email', 180)->index();
            $table->string('name', 160)->nullable();
            $table->string('organization', 180)->nullable();
            $table->string('role_requested', 64)->default('guest');
            $table->text('request_message')->nullable();
            $table->string('delivered_to', 180)->default('eabventures@gmail.com');
            $table->string('status', 32)->default('active')->index(); // active | used | revoked | expired
            $table->unsignedInteger('times_used')->default(0);
            $table->unsignedInteger('max_uses')->default(25);
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_security_codes');
    }
};
