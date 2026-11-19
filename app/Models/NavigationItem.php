<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class NavigationItem extends Model
{
    protected $fillable = [
        'key',
        'label',
        'url',
        'route_name',
        'target',
        'audience',
        'is_active',
        'is_visible',
        'is_cta',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'is_cta' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Resolves the navigation item's URL (route or absolute/relative URL).
     */
    public function resolvedUrl(): string
    {
        if (! empty($this->route_name) && Route::has($this->route_name)) {
            try {
                return route($this->route_name);
            } catch (\Throwable $e) {
                // fallback to url below
            }
        }

        if (preg_match('#^https?://#i', (string) $this->url)) {
            return $this->url;
        }

        return url($this->url ?: '/');
    }

    /**
     * Determines if this navigation item is the current active destination.
     */
    public function isCurrent(): bool
    {
        if (! empty($this->route_name) && request()->routeIs($this->route_name)) {
            return true;
        }

        $currentPath = trim(request()->path(), '/');
        $targetPath = trim((string) parse_url((string) $this->url, PHP_URL_PATH), '/');

        if ($targetPath === '' && $currentPath === '') {
            return true;
        }

        return $targetPath !== '' && $currentPath === $targetPath;
    }

    /**
     * Returns the active & visible navigation items for the current request / authenticated role.
     */
    public static function getForCurrentRequest()
    {
        try {
            if (! Schema::hasTable('navigation_items')) {
                return collect([]);
            }

            $user = auth()->user();
            $role = $user?->role;

            $query = static::query()
                ->where('is_active', true)
                ->where('is_visible', true)
                ->orderBy('sort_order')
                ->orderBy('id');

            if ($role === null) {
                $query->whereIn('audience', ['guest', 'all']);
            } else {
                $query->whereIn('audience', [$role, 'auth', 'all']);
            }

            return $query->get();
        } catch (\Throwable $e) {
            return collect([]);
        }
    }

    /**
     * Resets navigation items back to initial platform defaults.
     */
    public static function resetToDefaults(): void
    {
        static::query()->delete();

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
}
