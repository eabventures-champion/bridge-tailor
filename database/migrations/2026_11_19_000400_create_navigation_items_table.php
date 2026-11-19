<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->nullable()->index();
            $table->string('label', 160);
            $table->string('url', 255);
            $table->string('route_name', 120)->nullable();
            $table->string('target', 20)->default('_self');
            $table->string('audience', 30)->default('guest')->index(); // guest, auth, client, consultant, admin, developer, all
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->boolean('is_cta')->default(false);
            $table->boolean('is_system')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            // Guest Navigation Items
            ['key' => 'guest_overview', 'label' => 'Overview', 'url' => '/', 'route_name' => 'diagnostic.landing', 'target' => '_self', 'audience' => 'guest', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'guest_wizard', 'label' => 'Business Health Test', 'url' => '/wizard', 'route_name' => 'diagnostic.wizard', 'target' => '_self', 'audience' => 'guest', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'guest_login', 'label' => 'Sign In', 'url' => '/login', 'route_name' => 'login', 'target' => '_self', 'audience' => 'guest', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'guest_start_assessment', 'label' => 'Start Assessment →', 'url' => '/wizard', 'route_name' => 'diagnostic.wizard', 'target' => '_self', 'audience' => 'guest', 'is_active' => true, 'is_visible' => true, 'is_cta' => true, 'is_system' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],

            // Client Navigation Items
            ['key' => 'client_portal', 'label' => 'My Companies & Action Tracker', 'url' => '/portal', 'route_name' => 'portal.index', 'target' => '_self', 'audience' => 'client', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'client_assessment', 'label' => '+ New Business Assessment →', 'url' => '/wizard', 'route_name' => 'diagnostic.wizard', 'target' => '_self', 'audience' => 'client', 'is_active' => true, 'is_visible' => true, 'is_cta' => true, 'is_system' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],

            // Consultant Navigation Items
            ['key' => 'consultant_desk', 'label' => 'Consultant Review Desk & CRM', 'url' => '/consultant', 'route_name' => 'consultant.dashboard', 'target' => '_self', 'audience' => 'consultant', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'consultant_portal', 'label' => 'Client Portfolios', 'url' => '/portal', 'route_name' => 'portal.index', 'target' => '_self', 'audience' => 'consultant', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'consultant_wizard', 'label' => '+ Assisted Diagnostic →', 'url' => '/wizard', 'route_name' => 'diagnostic.wizard', 'target' => '_self', 'audience' => 'consultant', 'is_active' => true, 'is_visible' => true, 'is_cta' => true, 'is_system' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],

            // Admin Navigation Items
            ['key' => 'admin_console', 'label' => 'Admin Console', 'url' => '/admin', 'route_name' => 'admin.console', 'target' => '_self', 'audience' => 'admin', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'admin_consultant_desk', 'label' => 'Consultant Desk & CRM', 'url' => '/consultant', 'route_name' => 'consultant.dashboard', 'target' => '_self', 'audience' => 'admin', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'admin_framework', 'label' => 'Framework & Questions Config', 'url' => '/consultant/framework', 'route_name' => 'consultant.framework', 'target' => '_self', 'audience' => 'admin', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'admin_portal', 'label' => 'Client Portfolios', 'url' => '/portal', 'route_name' => 'portal.index', 'target' => '_self', 'audience' => 'admin', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'admin_wizard', 'label' => '+ New Diagnostic →', 'url' => '/wizard', 'route_name' => 'diagnostic.wizard', 'target' => '_self', 'audience' => 'admin', 'is_active' => true, 'is_visible' => true, 'is_cta' => true, 'is_system' => true, 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],

            // Developer Navigation Items
            ['key' => 'developer_console', 'label' => 'Developer Console', 'url' => '/developer', 'route_name' => 'developer.console', 'target' => '_self', 'audience' => 'developer', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'developer_overview', 'label' => 'Overview', 'url' => '/', 'route_name' => 'diagnostic.landing', 'target' => '_self', 'audience' => 'developer', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'developer_consultant_desk', 'label' => 'Consultant Desk', 'url' => '/consultant', 'route_name' => 'consultant.dashboard', 'target' => '_self', 'audience' => 'developer', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'developer_framework', 'label' => 'Framework JSON', 'url' => '/consultant/framework', 'route_name' => 'consultant.framework', 'target' => '_self', 'audience' => 'developer', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'developer_portal', 'label' => 'Client Portal', 'url' => '/portal', 'route_name' => 'portal.index', 'target' => '_self', 'audience' => 'developer', 'is_active' => true, 'is_visible' => true, 'is_cta' => false, 'is_system' => true, 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'developer_wizard', 'label' => '+ New Assessment →', 'url' => '/wizard', 'route_name' => 'diagnostic.wizard', 'target' => '_self', 'audience' => 'developer', 'is_active' => true, 'is_visible' => true, 'is_cta' => true, 'is_system' => true, 'sort_order' => 60, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('navigation_items')->insert($defaults);
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
