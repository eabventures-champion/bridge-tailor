@extends('layouts.app')

@section('title', ($isDeveloper ? 'Developer Master Console' : 'Admin Console') . ' — Dynamic Text, Visibility & User Directory')

@section('content')
<!-- Section 1: Solid Espresso Console Header & Master KPI Strip -->
<section class="sec-espresso">
    <div class="bt-container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:14px;">
            <div>
                <div class="eyebrow">{{ $isDeveloper ? 'DEVELOPER MASTER CONSOLE · UNRESTRICTED PLATFORM CONTROL' : 'BRIDGE-TAILOR ADMIN CONSOLE · PLATFORM & USER MANAGEMENT' }}</div>
                <h1 style="font-size:28px;color:#fff;">Dynamic UI Text, Component Visibility &amp; User Directory</h1>
                <div style="font-size:12.5px;color:#d6cdc2;margin-top:4px;">
                    Edit any platform text dynamically, show or hide sections/cards in real time, and inspect or manage {{ $isDeveloper ? 'every Client, Consultant, Admin, and Developer' : 'Client, Consultant, and Admin' }} account.
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                <button type="button" class="btn btn-outline btn-sm" onclick="expandAllConsoleSections()" style="background:#141210;color:#e6dfd8;border-color:#5a514b;">
                    + Expand All Sections
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="collapseAllConsoleSections()" style="background:#141210;color:#e6dfd8;border-color:#5a514b;">
                    − Collapse All Sections
                </button>
                <form action="{{ route($routePrefix . '.reset') }}" method="POST" style="margin:0;" onsubmit="try{localStorage.removeItem('bt_console_expanded_sections');sessionStorage.removeItem('bt_console_last_saved_section');}catch(e){}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="background:#141210;color:#fff;border-color:#786e67;">
                        ↺ Reset Defaults
                    </button>
                </form>
                <a href="{{ route('consultant.framework') }}" class="btn btn-amber btn-sm">Framework JSON Engine →</a>
            </div>
        </div>

        <div class="flat-grid-5" style="border-color:#443e3a;background:#1c1917;">
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:var(--bt-amber);">{{ $stats['users_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">
                    Users ({{ $stats['clients_count'] }} Client · {{ $stats['consultants_count'] }} Consult · {{ $stats['admins_count'] }} {{ $isDeveloper ? 'Admin/Dev' : 'Admin' }})
                </div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:#fff;">{{ $stats['companies_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">Registered Client Companies</div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:#fff;">{{ $stats['assessments_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">Total Diagnostic Assessments</div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:#7fd4a6;">{{ $stats['visible_components'] }} / {{ $stats['total_components'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">UI Components Currently Visible</div>
            </div>
            <div style="padding:10px 14px;">
                <div style="font-size:22px;font-weight:800;color:var(--bt-amber);">{{ $stats['consultations_total'] }} · {{ $stats['actions_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">CRM Leads &amp; Tracked Actions</div>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Solid Cream Band — Show / Hide Platform Components & Cards -->
<section class="sec-cream" id="dev-visibility">
    <div class="bt-container">
        <form action="{{ route($routePrefix . '.visibility.update') }}" method="POST">
            @csrf
            <div class="sec-header" style="cursor:pointer;" onclick="toggleSectionCollapse('dev-visibility-content', 'dev-visibility-btn', 'dev-visibility-badge')">
                <div>
                    <div class="eyebrow">01 · DYNAMIC COMPONENT &amp; CARD VISIBILITY CONTROLLER</div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <h2 style="font-size:23px;margin:0;">Show or Hide Sections, Cards &amp; UI Modules Across the Platform</h2>
                        <span class="tag tag-solid" id="dev-visibility-badge">COLLAPSED</span>
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;" onclick="event.stopPropagation()">
                    <button type="button" class="btn btn-outline btn-sm" id="dev-visibility-btn" onclick="toggleSectionCollapse('dev-visibility-content', 'dev-visibility-btn', 'dev-visibility-badge')">
                        + Expand Section ▼
                    </button>
                    <button type="submit" class="btn btn-amber btn-sm">Save Visibility Toggles →</button>
                </div>
            </div>

            <div id="dev-visibility-content" style="display:none;margin-top:14px;">
                <div class="flat-grid-2">
                    @foreach($visibilitySettings as $key => $item)
                        @php
                            $isVisible = in_array((string) $item['current'], ['1', 'true', 'yes', 'on'], true);
                        @endphp
                        <label class="flat-box" style="display:flex;justify-content:space-between;align-items:center;gap:12px;cursor:pointer;background:{{ $isVisible ? 'var(--bt-white)' : '#efe8df' }};border-left:4px solid {{ $isVisible ? 'var(--bt-teal)' : '#9E2A1C' }};">
                            <div>
                                <div style="font-weight:700;font-size:13px;">{{ $item['label'] }}</div>
                                <div style="font-size:10.5px;color:#666;margin-top:2px;">Key: <code>{{ $key }}</code></div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                <span class="tag {{ $isVisible ? 'tag-ok' : 'tag-high' }}">{{ $isVisible ? 'VISIBLE' : 'HIDDEN' }}</span>
                                <input type="checkbox" name="vis[{{ $key }}]" value="1" {{ $isVisible ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                            </div>
                        </label>
                    @endforeach
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:12px;">
                    <button type="submit" class="btn btn-amber">Apply Show / Hide Settings →</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Section 01B: Solid Slate Band — Dynamic Navigation Menu CMS, Visibility & Total Removal -->
<section class="sec-slate" id="dev-navigation-menu">
    <div class="bt-container">
        <div class="sec-header" style="cursor:pointer;" onclick="toggleSectionCollapse('dev-navigation-menu-content', 'dev-navigation-menu-btn', 'dev-navigation-menu-badge')">
            <div>
                <div class="eyebrow">01B · DYNAMIC NAVIGATION MENU CMS, VISIBILITY &amp; TOTAL REMOVAL</div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <h2 style="font-size:23px;margin:0;">Header Navigation: Hide / Show, Make Active Dynamic Menus &amp; Total Removal</h2>
                    <span class="tag tag-solid" id="dev-navigation-menu-badge">COLLAPSED</span>
                </div>
                <div style="font-size:12.5px;color:#555;margin-top:4px;">
                    Control top-level menu items (including the highlighted <strong>Overview</strong>). Hide or enable to show, activate/deactivate dynamic items, add custom links, or execute total removal.
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;" onclick="event.stopPropagation()">
                <button type="button" class="btn btn-outline btn-sm" id="dev-navigation-menu-btn" onclick="toggleSectionCollapse('dev-navigation-menu-content', 'dev-navigation-menu-btn', 'dev-navigation-menu-badge')">
                    + Expand Section ▼
                </button>
                <button type="button" class="btn btn-amber btn-sm" onclick="ensureExpandedAndToggle('dev-navigation-menu-content', 'dev-navigation-menu-btn', 'dev-navigation-menu-badge', 'add-menu-drawer')">+ Add Dynamic Menu Item →</button>
                <form action="{{ route($routePrefix . '.menu.reset') }}" method="POST" style="margin:0;" onsubmit="return confirm('Restore all system navigation items to initial platform defaults?');">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">↺ Reset Menu to Defaults</button>
                </form>
            </div>
        </div>

        <div id="dev-navigation-menu-content" style="display:none;margin-top:14px;">
            <!-- Add Dynamic Menu Item Drawer / Card -->
        <div id="add-menu-drawer" class="flat-box" style="display:none;background:#fff;margin-bottom:16px;border-left:4px solid var(--bt-amber);">
            <div style="font-size:14px;font-weight:700;margin-bottom:12px;color:var(--bt-ink);">+ Create New Dynamic Navigation Menu Link</div>
            <form action="{{ route($routePrefix . '.menu.store') }}" method="POST">
                @csrf
                <div class="flat-grid-4">
                    <div class="field">
                        <label>Menu Label *</label>
                        <input type="text" name="label" required placeholder="e.g. Advisory Insights">
                    </div>
                    <div class="field">
                        <label>Destination URL *</label>
                        <input type="text" name="url" required placeholder="e.g. /about or https://...">
                    </div>
                    <div class="field">
                        <label>Target Audience *</label>
                        <select name="audience">
                            <option value="guest">Guest Only (Public Header)</option>
                            <option value="all">Everyone (Guest &amp; Logged In)</option>
                            <option value="auth">All Logged-In Users</option>
                            <option value="client">Client Accounts Only</option>
                            <option value="consultant">Consultants Only</option>
                            <option value="admin">Administrators Only</option>
                            @if($isDeveloper)
                                <option value="developer">Developers Only</option>
                            @endif
                        </select>
                    </div>
                    <div class="field">
                        <label>Open In</label>
                        <select name="target">
                            <option value="_self">Same Window (_self)</option>
                            <option value="_blank">New Tab (_blank)</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;margin:10px 0 14px;">
                    <label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" checked style="width:16px;height:16px;accent-color:var(--bt-amber);">
                        <span><strong>Make Active</strong> (operational in dynamic menu)</span>
                    </label>
                    <label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                        <input type="checkbox" name="is_visible" value="1" checked style="width:16px;height:16px;accent-color:var(--bt-amber);">
                        <span><strong>Enable to Show</strong> (visible to visitors)</span>
                    </label>
                    <label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                        <input type="checkbox" name="is_cta" value="1" style="width:16px;height:16px;accent-color:var(--bt-amber);">
                        <span>Highlight as CTA Button (Solid Amber)</span>
                    </label>
                    <div style="display:inline-flex;align-items:center;gap:6px;font-size:12px;">
                        <span>Sort Order:</span>
                        <input type="number" name="sort_order" value="50" style="width:60px;padding:4px 6px;">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleElement('add-menu-drawer')">Cancel</button>
                    <button type="submit" class="btn btn-amber btn-sm">+ Publish Dynamic Menu Link →</button>
                </div>
            </form>
        </div>

        <!-- Filter Pills -->
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
            <button type="button" class="btn btn-sm menu-filter-btn active-filter" onclick="filterMenuItems('all', this)" style="padding:4px 10px;font-size:11px;background:var(--bt-espresso);color:#fff;">ALL ({{ $navigationItems->count() }})</button>
            <button type="button" class="btn btn-sm menu-filter-btn" onclick="filterMenuItems('guest', this)" style="padding:4px 10px;font-size:11px;background:#fff;color:#141210;">GUEST (Overview &amp; Public)</button>
            <button type="button" class="btn btn-sm menu-filter-btn" onclick="filterMenuItems('client', this)" style="padding:4px 10px;font-size:11px;background:#fff;color:#141210;">CLIENT</button>
            <button type="button" class="btn btn-sm menu-filter-btn" onclick="filterMenuItems('consultant', this)" style="padding:4px 10px;font-size:11px;background:#fff;color:#141210;">CONSULTANT</button>
            <button type="button" class="btn btn-sm menu-filter-btn" onclick="filterMenuItems('admin', this)" style="padding:4px 10px;font-size:11px;background:#fff;color:#141210;">ADMIN</button>
            @if($isDeveloper)
                <button type="button" class="btn btn-sm menu-filter-btn" onclick="filterMenuItems('developer', this)" style="padding:4px 10px;font-size:11px;background:#fff;color:#141210;">DEVELOPER</button>
            @endif
            <button type="button" class="btn btn-sm menu-filter-btn" onclick="filterMenuItems('custom', this)" style="padding:4px 10px;font-size:11px;background:#fff;color:#141210;">DYNAMIC CUSTOM</button>
        </div>

        <!-- Menu Items List Table -->
        <div style="overflow-x:auto;background:#fff;border:1px solid var(--bt-border);">
            <table class="bt-table" style="margin:0;font-size:12px;">
                <thead>
                    <tr style="background:#2c2927;color:#fff;">
                        <th style="width:24%;">MENU ITEM &amp; TARGET</th>
                        <th style="width:12%;">AUDIENCE</th>
                        <th style="width:10%;">TYPE</th>
                        <th style="width:12%;">ACTIVE STATUS</th>
                        <th style="width:12%;">VISIBILITY</th>
                        <th style="width:6%;">ORDER</th>
                        <th style="width:24%;text-align:right;">MANAGEMENT &amp; ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($navigationItems as $item)
                        @php
                            $isOverview = str_contains(strtolower($item->label), 'overview') || str_contains((string)$item->key, 'overview');
                            $itemType = $item->is_system ? 'system' : 'custom';
                        @endphp
                        <tr class="menu-item-row" data-audience="{{ $item->audience }}" data-type="{{ $itemType }}" style="{{ $isOverview ? 'background:#fffbf2;border-left:4px solid var(--bt-amber);' : '' }}">
                            <td data-label="Menu Item">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <strong style="font-size:13px;color:var(--bt-ink);">{{ $item->label }}</strong>
                                    @if($isOverview)
                                        <span class="tag tag-amber" style="font-size:9px;">HIGHLIGHTED</span>
                                    @endif
                                    @if($item->is_cta)
                                        <span class="tag tag-ok" style="font-size:9px;">CTA BUTTON</span>
                                    @endif
                                </div>
                                <div style="font-size:11px;color:#666;margin-top:2px;">
                                    URL: <code>{{ $item->url }}</code>
                                    @if($item->route_name)
                                        <span style="opacity:0.7;">(Route: {{ $item->route_name }})</span>
                                    @endif
                                    @if($item->target === '_blank')
                                        <span class="tag" style="font-size:8.5px;padding:1px 4px;">↗ NEW TAB</span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Audience">
                                <span class="tag" style="background:#ede8e2;color:#2c2927;text-transform:uppercase;font-weight:700;">{{ $item->audience }}</span>
                            </td>
                            <td data-label="Type">
                                <span class="tag {{ $item->is_system ? '' : 'tag-amber' }}">{{ $item->is_system ? 'CORE' : 'DYNAMIC' }}</span>
                            </td>
                            <td data-label="Active Status">
                                @if($item->is_active)
                                    <span class="tag tag-ok">ACTIVE</span>
                                @else
                                    <span class="tag tag-high">INACTIVE</span>
                                @endif
                            </td>
                            <td data-label="Visibility">
                                @if($item->is_visible)
                                    <span class="tag tag-ok">VISIBLE</span>
                                @else
                                    <span class="tag tag-high" style="background:#b32400;color:#fff;">HIDDEN</span>
                                @endif
                            </td>
                            <td data-label="Order">
                                <span>{{ $item->sort_order }}</span>
                            </td>
                            <td data-label="Actions" style="text-align:right;">
                                <div style="display:inline-flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;">
                                    <!-- Toggle Visibility Form (Hide / Enable to Show) -->
                                    <form action="{{ route($routePrefix . '.menu.toggle_visibility', $item->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="padding:3px 7px;font-size:10px;background:{{ $item->is_visible ? '#5c544f' : '#28a745' }};color:#fff;" title="{{ $item->is_visible ? 'Hide from navigation' : 'Enable to show in navigation' }}">
                                            {{ $item->is_visible ? 'Hide' : 'Enable Show' }}
                                        </button>
                                    </form>

                                    <!-- Toggle Active Form (Make Active / Inactive) -->
                                    <form action="{{ route($routePrefix . '.menu.toggle_active', $item->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="padding:3px 7px;font-size:10px;background:{{ $item->is_active ? '#786e67' : 'var(--bt-amber)' }};color:{{ $item->is_active ? '#fff' : 'var(--bt-ink)' }};" title="{{ $item->is_active ? 'Make inactive' : 'Make active' }}">
                                            {{ $item->is_active ? 'Deactivate' : 'Make Active' }}
                                        </button>
                                    </form>

                                    <!-- Quick Edit Trigger -->
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:3px 7px;font-size:10px;" onclick="toggleElement('edit-menu-{{ $item->id }}')">
                                        Edit
                                    </button>

                                    <!-- Total Removal Form -->
                                    <form action="{{ route($routePrefix . '.menu.delete', $item->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('WARNING: Are you sure you want to execute TOTAL REMOVAL of \'{{ addslashes($item->label) }}\'? This will permanently delete this item from the platform menu.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="padding:3px 7px;font-size:10px;background:#9E2A1C;color:#fff;" title="Total removal of this item">
                                            ✕ Total Removal
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Drawer Row -->
                        <tr id="edit-menu-{{ $item->id }}" style="display:none;background:#f9f6f2;border-bottom:2px solid var(--bt-amber);">
                            <td colspan="7" style="padding:14px;">
                                <form action="{{ route($routePrefix . '.menu.update', $item->id) }}" method="POST">
                                    @csrf
                                    <div style="font-weight:700;font-size:12px;margin-bottom:8px;color:var(--bt-ink);">Edit Menu Item: {{ $item->label }}</div>
                                    <div class="flat-grid-4">
                                        <div class="field">
                                            <label>Label</label>
                                            <input type="text" name="label" value="{{ $item->label }}" required>
                                        </div>
                                        <div class="field">
                                            <label>URL</label>
                                            <input type="text" name="url" value="{{ $item->url }}" required>
                                        </div>
                                        <div class="field">
                                            <label>Audience</label>
                                            <select name="audience">
                                                <option value="guest" {{ $item->audience === 'guest' ? 'selected' : '' }}>Guest Only</option>
                                                <option value="all" {{ $item->audience === 'all' ? 'selected' : '' }}>Everyone</option>
                                                <option value="auth" {{ $item->audience === 'auth' ? 'selected' : '' }}>All Logged In</option>
                                                <option value="client" {{ $item->audience === 'client' ? 'selected' : '' }}>Client</option>
                                                <option value="consultant" {{ $item->audience === 'consultant' ? 'selected' : '' }}>Consultant</option>
                                                <option value="admin" {{ $item->audience === 'admin' ? 'selected' : '' }}>Admin</option>
                                                @if($isDeveloper)
                                                    <option value="developer" {{ $item->audience === 'developer' ? 'selected' : '' }}>Developer</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label>Target</label>
                                            <select name="target">
                                                <option value="_self" {{ $item->target === '_self' ? 'selected' : '' }}>Same Window (_self)</option>
                                                <option value="_blank" {{ $item->target === '_blank' ? 'selected' : '' }}>New Tab (_blank)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;margin:8px 0 10px;">
                                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;cursor:pointer;">
                                            <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} style="accent-color:var(--bt-amber);">
                                            <span>Active</span>
                                        </label>
                                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;cursor:pointer;">
                                            <input type="checkbox" name="is_visible" value="1" {{ $item->is_visible ? 'checked' : '' }} style="accent-color:var(--bt-amber);">
                                            <span>Visible</span>
                                        </label>
                                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;cursor:pointer;">
                                            <input type="checkbox" name="is_cta" value="1" {{ $item->is_cta ? 'checked' : '' }} style="accent-color:var(--bt-amber);">
                                            <span>CTA Button Style</span>
                                        </label>
                                        <div style="display:inline-flex;align-items:center;gap:6px;font-size:11.5px;">
                                            <span>Order:</span>
                                            <input type="number" name="sort_order" value="{{ $item->sort_order }}" style="width:55px;padding:3px;">
                                        </div>
                                    </div>
                                    <div style="display:flex;justify-content:flex-end;gap:6px;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleElement('edit-menu-{{ $item->id }}')">Cancel</button>
                                        <button type="submit" class="btn btn-amber btn-sm">Save Changes →</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>
</section>

<!-- Section 3: Solid White Band — Dynamic Text & Copy CMS Editor -->
<section class="sec-white" id="dev-text">
    <div class="bt-container">
        <form action="{{ route($routePrefix . '.text.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="sec-header" style="cursor:pointer;" onclick="toggleSectionCollapse('dev-text-content', 'dev-text-btn', 'dev-text-badge')">
                <div>
                    <div class="eyebrow">02 · DYNAMIC PLATFORM TEXT &amp; HEADLINE CMS</div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <h2 style="font-size:23px;margin:0;">Dynamically Edit Platform Headlines, Labels, Callouts, Slider Images &amp; Copy</h2>
                        <span class="tag tag-solid" id="dev-text-badge">COLLAPSED</span>
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;" onclick="event.stopPropagation()">
                    <button type="button" class="btn btn-outline btn-sm" id="dev-text-btn" onclick="toggleSectionCollapse('dev-text-content', 'dev-text-btn', 'dev-text-badge')">
                        + Expand Section ▼
                    </button>
                    <button type="submit" class="btn btn-amber btn-sm">Save All Text Overrides →</button>
                </div>
            </div>

            <div id="dev-text-content" style="display:none;margin-top:14px;">
                <div class="flat-grid-2">
                    @foreach($textSettings as $key => $item)
                        <div class="flat-box" style="background:var(--bt-slate);">
                            <div class="field" style="margin-bottom:0;">
                                <label>{{ $item['label'] }} <span style="font-weight:400;opacity:0.7;">({{ $key }})</span></label>
                                @if((str_contains($key, 'slide_') && str_contains($key, '_image')) || str_contains($key, 'login_welcome_image'))
                                    <div style="display:flex;gap:10px;align-items:center;margin-bottom:6px;">
                                        @php
                                            $currImg = $item['current'] ?? '';
                                            $previewUrl = str_starts_with($currImg, 'http') ? $currImg : asset(ltrim($currImg, '/'));
                                        @endphp
                                        <img src="{{ $previewUrl }}" alt="Preview" style="height:44px;width:72px;object-fit:cover;border:1px solid #cbd5e1;background:#e2e8f0;flex-shrink:0;">
                                        <input type="text" name="texts[{{ $key }}]" value="{{ $item['current'] }}" placeholder="{{ $item['placeholder'] ?? 'e.g. images/slider/slide-1.jpg or https://...' }}" style="flex:1;">
                                    </div>
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        <span style="font-size:10.5px;font-weight:700;color:var(--bt-umber);text-transform:uppercase;">Or upload replacement file:</span>
                                        <input type="file" name="slide_uploads[{{ $key }}]" accept="image/*" style="font-size:11px;padding:3px 6px;">
                                    </div>
                                @elseif(strlen((string) $item['current']) > 110)
                                    <textarea name="texts[{{ $key }}]" rows="3" placeholder="{{ $item['placeholder'] ?? '' }}">{{ $item['current'] }}</textarea>
                                @else
                                    <input type="text" name="texts[{{ $key }}]" value="{{ $item['current'] }}" placeholder="{{ $item['placeholder'] ?? '' }}">
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:14px;">
                    <button type="submit" class="btn btn-amber">Publish Dynamic Text Changes →</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Section 4: Solid Slate Band — User Directory -->
<section class="sec-slate" id="dev-users">
    <div class="bt-container">
        <div class="sec-header" style="cursor:pointer;" onclick="toggleSectionCollapse('dev-users-content', 'dev-users-btn', 'dev-users-badge')">
            <div>
                <div class="eyebrow">03 · USER DIRECTORY — {{ $isDeveloper ? 'CLIENTS, CONSULTANTS, ADMINS & DEVELOPERS' : 'CLIENTS, CONSULTANTS & ADMINS' }}</div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <h2 style="font-size:23px;margin:0;">Inspect &amp; Edit Every User Account, Role, Contact Detail &amp; Password</h2>
                    <span class="tag tag-solid" id="dev-users-badge">COLLAPSED</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;" onclick="event.stopPropagation()">
                <button type="button" class="btn btn-outline btn-sm" id="dev-users-btn" onclick="toggleSectionCollapse('dev-users-content', 'dev-users-btn', 'dev-users-badge')">
                    + Expand Section ▼
                </button>
            </div>
        </div>

        <div id="dev-users-content" style="display:none;margin-top:14px;">
            <table class="bt-table" style="margin-bottom:16px;">
            <thead>
                <tr>
                    <th style="width:5%;">ID</th>
                    <th style="width:12%;">Role</th>
                    <th style="width:28%;">User Details (Editable Inline)</th>
                    <th style="width:24%;">Associated Companies &amp; Activity</th>
                    <th style="width:31%;">Role, Title &amp; Password Reset</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td style="font-weight:800;">#{{ $u->id }}</td>
                        <td>
                            <span class="tag {{ $u->role === 'developer' ? 'tag-amber' : ($u->role === 'admin' ? 'tag-critical' : ($u->role === 'consultant' ? 'tag-solid' : 'tag-ok')) }}">
                                {{ strtoupper($u->role) }}
                            </span>
                            <div style="font-size:10px;color:#555;margin-top:4px;">Created {{ $u->created_at?->format('d M Y') }}</div>
                        </td>
                        <td colspan="3">
                            <form action="{{ route('developer.users.update', $u->id) }}" method="POST" class="bt-dev-user-form">
                                @csrf
                                <div>
                                    <div class="field" style="margin-bottom:6px;">
                                        <label>Full Name</label>
                                        <input type="text" name="name" value="{{ $u->name }}" required>
                                    </div>
                                    <div class="field" style="margin-bottom:6px;">
                                        <label>Email Address</label>
                                        <input type="email" name="email" value="{{ $u->email }}" required>
                                    </div>
                                    <div class="field" style="margin-bottom:0;">
                                        <label>Phone</label>
                                        <input type="text" name="phone" value="{{ $u->phone }}" placeholder="+233 ...">
                                    </div>
                                </div>

                                <div style="font-size:11.5px;background:#fff;border:1px solid var(--bt-border);padding:8px 10px;">
                                    <div style="font-weight:800;text-transform:uppercase;font-size:10px;color:var(--bt-umber);margin-bottom:4px;">Linked Enterprises</div>
                                    @if($u->role === 'client')
                                        @forelse($u->ownedCompanies as $comp)
                                            <div style="margin-bottom:3px;">
                                                ■ <a href="{{ route('portal.index', ['company' => $comp->ulid]) }}" style="text-decoration:underline;font-weight:700;">{{ $comp->name }}</a>
                                                ({{ $comp->sector }} · {{ $comp->stage }})
                                            </div>
                                        @empty
                                            <div style="color:#777;">No owned companies yet.</div>
                                        @endforelse
                                    @elseif($u->isConsultant())
                                        <div>Assigned Companies: <strong>{{ $u->assignedCompanies->count() }}</strong></div>
                                        @foreach($u->assignedCompanies as $ac)
                                            <div style="margin-top:2px;">■ {{ $ac->name }}</div>
                                        @endforeach
                                    @endif
                                </div>

                                <div>
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:6px;">
                                        <div class="field" style="margin-bottom:0;">
                                            <label>Role</label>
                                            <select name="role">
                                                @foreach($availableRoles as $rK => $rL)
                                                    <option value="{{ $rK }}" {{ $u->role === $rK ? 'selected' : '' }}>{{ $rL }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field" style="margin-bottom:0;">
                                            <label>Job Title</label>
                                            <input type="text" name="job_title" value="{{ $u->job_title }}">
                                        </div>
                                    </div>
                                    <div class="field" style="margin-bottom:6px;">
                                        <label>Set New Password (leave blank to keep current)</label>
                                        <input type="password" name="new_password" placeholder="New password...">
                                    </div>
                                    <button type="submit" class="btn btn-sm" style="width:100%;">Update {{ $u->name }} →</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Create New User -->
        <div class="flat-box">
            <div class="eyebrow">CREATE NEW PLATFORM USER ({{ $isDeveloper ? 'CLIENT, CONSULTANT, ADMIN, OR DEVELOPER' : 'CLIENT, CONSULTANT, OR ADMIN' }})</div>
            <form action="{{ route('developer.users.store') }}" method="POST">
                @csrf
                <div class="flat-grid-3">
                    <div class="field">
                        <label>Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Kofi Boateng">
                    </div>
                    <div class="field">
                        <label>Email Address *</label>
                        <input type="email" name="email" required placeholder="kofi@bridge-tailor.com">
                    </div>
                    <div class="field">
                        <label>Assign Role *</label>
                        <select name="role">
                            <option value="client">Client (Multi-Company Account)</option>
                            <option value="consultant">Consultant (Review Desk &amp; CRM)</option>
                            <option value="admin">Admin (Admin Console + Framework Config)</option>
                            @if($isDeveloper)
                                <option value="developer">Developer (Full Master Console)</option>
                            @endif
                        </select>
                    </div>
                    <div class="field">
                        <label>Job Title</label>
                        <input type="text" name="job_title" placeholder="e.g. Senior Consultant">
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="+233 ...">
                    </div>
                    <div class="field">
                        <label>Initial Password *</label>
                        <input type="password" name="password" required placeholder="Minimum 6 characters">
                    </div>
                </div>
                <button type="submit" class="btn btn-amber btn-sm">+ Create Platform User Account</button>
            </form>
        </div>
        </div>
    </div>
</section>

<!-- Section 5: Solid Cream Band — All Registered Companies, Manual Consultant Assignment & Automated Routing Engine -->
<section class="sec-cream" id="dev-enterprises">
    <div class="bt-container">
        <div class="sec-header" style="cursor:pointer;" onclick="toggleSectionCollapse('dev-enterprises-content', 'dev-enterprises-btn', 'dev-enterprises-badge')">
            <div>
                <div class="eyebrow">04 · ALL ENTERPRISES, CONSULTANT ASSIGNMENT &amp; AUTOMATED ROUTING</div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <h2 style="font-size:22px;margin:0;">Complete Enterprise Directory, Manual Assignment &amp; Auto-Routing Engine</h2>
                    <span class="tag tag-solid" id="dev-enterprises-badge">COLLAPSED</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;" onclick="event.stopPropagation()">
                <button type="button" class="btn btn-outline btn-sm" id="dev-enterprises-btn" onclick="toggleSectionCollapse('dev-enterprises-content', 'dev-enterprises-btn', 'dev-enterprises-badge')">
                    + Expand Section ▼
                </button>
                @if($unassignedCompaniesCount > 0)
                    <form action="{{ route('developer.companies.auto_assign_all') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-amber btn-sm">
                            ⚡ Auto-Assign {{ $unassignedCompaniesCount }} Unassigned {{ Str::plural('Company', $unassignedCompaniesCount) }} Now →
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div id="dev-enterprises-content" style="display:none;margin-top:14px;">
            <!-- Automated Consultant Routing Engine Control Box -->
        <div class="flat-box" style="margin-bottom:14px;background:var(--bt-white);border-left:5px solid {{ $autoAssignEnabled ? 'var(--bt-teal)' : 'var(--bt-amber)' }};">
            <form action="{{ route('developer.assignment.settings') }}" method="POST">
                @csrf
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;margin-bottom:10px;">
                    <div>
                        <div class="eyebrow">AUTOMATED CONSULTANT ROUTING ENGINE (WHEN ADMIN IS UNAVAILABLE)</div>
                        <h3 style="font-size:16px;">Automatic Client-to-Consultant Assignment Configuration</h3>
                        <p style="font-size:12px;color:#443e3a;margin:3px 0 0;">
                            When enabled, every new business that completes a diagnostic assessment or books a consultation is automatically assigned to a consultant immediately, even when an Administrator is offline.
                        </p>
                    </div>
                    <span class="tag {{ $autoAssignEnabled ? 'tag-ok' : 'tag-medium' }}" style="font-size:11px;padding:4px 9px;">
                        {{ $autoAssignEnabled ? '● AUTO-ASSIGNMENT ACTIVE' : '○ MANUAL ASSIGNMENT ONLY' }}
                    </span>
                </div>

                <div class="flat-grid-3" style="align-items:end;">
                    <div class="field" style="margin-bottom:0;">
                        <label>Automation Mode</label>
                        <select name="auto_assign_enabled">
                            <option value="1" {{ $autoAssignEnabled ? 'selected' : '' }}>Enabled — Automatically assign incoming businesses</option>
                            <option value="0" {{ !$autoAssignEnabled ? 'selected' : '' }}>Disabled — Wait for Admin to assign manually</option>
                        </select>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Routing Algorithm</label>
                        <select name="auto_assign_strategy">
                            <option value="least_loaded" {{ $autoAssignStrategy === 'least_loaded' ? 'selected' : '' }}>Workload-Balanced (Assign to consultant with fewest clients)</option>
                            <option value="round_robin" {{ $autoAssignStrategy === 'round_robin' ? 'selected' : '' }}>Round-Robin Rotation (Rotate evenly in sequence)</option>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        @if($unassignedCompaniesCount > 0)
                            <label style="font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="assign_existing_unassigned" value="1" checked>
                                Also auto-assign {{ $unassignedCompaniesCount }} currently unassigned {{ Str::plural('company', $unassignedCompaniesCount) }}
                            </label>
                        @endif
                        <button type="submit" class="btn btn-sm" style="width:100%;">Save Auto-Assignment Rules →</button>
                    </div>
                </div>
            </form>
        </div>

        <table class="bt-table">
            <thead>
                <tr>
                    <th style="width:20%;">Company Name</th>
                    <th style="width:18%;">Sector / Stage / Revenue</th>
                    <th style="width:20%;">Client Owner &amp; Contact</th>
                    <th style="width:20%;">Assigned Consultant (Admin Assign)</th>
                    <th style="width:10%;">Activity</th>
                    <th style="width:12%;">Workspace Links</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $comp)
                    @php $latestAss = $comp->assessments()->latest('id')->first(); @endphp
                    <tr>
                        <td style="font-weight:800;">{{ $comp->name }}</td>
                        <td style="font-size:11.5px;">
                            <div><strong>{{ $fw->sectors()[$comp->sector] ?? $comp->sector }}</strong></div>
                            <div>{{ $fw->stages()[$comp->stage] ?? $comp->stage }} · {{ $fw->get("revenue_bands.{$comp->revenue_band}", $comp->revenue_band) }}</div>
                        </td>
                        <td style="font-size:11.5px;">
                            <div><strong>{{ $comp->contact_name }}</strong> ({{ $comp->contact_email }})</div>
                            <div>{{ $comp->contact_phone }} · Owner: {{ $comp->owner?->name ?? 'Unassigned' }}</div>
                        </td>
                        <td style="font-size:11.5px;">
                            <form action="{{ route('developer.companies.assign', $comp->id) }}" method="POST" style="display:flex;gap:4px;align-items:center;margin:0;">
                                @csrf
                                <select name="assigned_consultant_id" style="border:1px solid #b8aea2;padding:5px 7px;font-size:11.5px;font-family:inherit;background:#fff;flex:1;font-weight:700;">
                                    <option value="">— Unassigned —</option>
                                    @foreach($assignableConsultants as $cons)
                                        <option value="{{ $cons->id }}" {{ (int)$comp->assigned_consultant_id === $cons->id ? 'selected' : '' }}>
                                            {{ $cons->name }} ({{ ucfirst($cons->role) }} · {{ $cons->assigned_companies_count }} clients)
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm">Assign</button>
                            </form>
                            @if($comp->consultant)
                                <div style="margin-top:3px;font-size:10.5px;color:var(--bt-success);font-weight:700;">
                                    ■ Assigned to {{ $comp->consultant->name }}
                                </div>
                            @else
                                <div style="margin-top:3px;font-size:10.5px;color:var(--bt-danger);font-weight:700;">
                                    ▲ Unassigned — select above or click Auto-Assign
                                </div>
                            @endif
                        </td>
                        <td style="font-size:11.5px;font-weight:700;">
                            {{ $comp->assessments_count }} runs · {{ $comp->documents_count }} docs · {{ $comp->action_items_count }} actions
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                <a href="{{ route('portal.index', ['company' => $comp->ulid]) }}" class="btn btn-sm">Client Portal</a>
                                @if($latestAss)
                                    <a href="{{ route('diagnostic.report', $latestAss->ulid) }}" class="btn btn-outline btn-sm">Report ({{ $latestAss->health_index }}/100)</a>
                                    <a href="{{ route('consultant.review', $latestAss->ulid) }}" class="btn btn-amber btn-sm">Review Mode</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</section>

<script>
const ALL_CONSOLE_SECTIONS = ['dev-visibility', 'dev-navigation-menu', 'dev-text', 'dev-users', 'dev-enterprises'];
const STORAGE_KEY = 'bt_console_expanded_sections';
const LAST_SAVED_KEY = 'bt_console_last_saved_section';

function getSavedExpandedSections() {
    try {
        const data = localStorage.getItem(STORAGE_KEY);
        return data ? JSON.parse(data) : [];
    } catch (e) {
        return [];
    }
}

function saveExpandedSections(sections) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(sections));
    } catch (e) {}
}

function setSectionState(sec, expanded) {
    const content = document.getElementById(sec + '-content');
    const btn = document.getElementById(sec + '-btn');
    const badge = document.getElementById(sec + '-badge');
    if (!content) return;

    content.style.display = expanded ? 'block' : 'none';

    if (btn) {
        btn.innerHTML = expanded 
            ? '<span>− Collapse Section ▲</span>' 
            : '<span>+ Expand Section ▼</span>';
        if (expanded) {
            btn.classList.add('btn-amber');
            btn.classList.remove('btn-outline');
        } else {
            btn.classList.remove('btn-amber');
            btn.classList.add('btn-outline');
        }
    }
    if (badge) {
        badge.textContent = expanded ? 'EXPANDED' : 'COLLAPSED';
        badge.className = expanded ? 'tag tag-ok' : 'tag tag-solid';
    }
}

function toggleSectionCollapse(contentId, btnId, badgeId) {
    const content = document.getElementById(contentId);
    if (!content) return;

    const isHidden = (content.style.display === 'none' || content.style.display === '');
    const willBeExpanded = isHidden;

    const secPrefix = contentId.replace(/-content$/, '');
    setSectionState(secPrefix, willBeExpanded);

    let saved = getSavedExpandedSections();
    if (willBeExpanded) {
        if (!saved.includes(secPrefix)) saved.push(secPrefix);
    } else {
        saved = saved.filter(s => s !== secPrefix);
    }
    saveExpandedSections(saved);
}

function ensureExpandedAndToggle(sectionContentId, sectionBtnId, sectionBadgeId, drawerId) {
    const content = document.getElementById(sectionContentId);
    if (content && (content.style.display === 'none' || content.style.display === '')) {
        toggleSectionCollapse(sectionContentId, sectionBtnId, sectionBadgeId);
    }
    toggleElement(drawerId);
}

function expandAllConsoleSections() {
    ALL_CONSOLE_SECTIONS.forEach(sec => setSectionState(sec, true));
    saveExpandedSections([...ALL_CONSOLE_SECTIONS]);
}

function collapseAllConsoleSections() {
    ALL_CONSOLE_SECTIONS.forEach(sec => setSectionState(sec, false));
    saveExpandedSections([]);
    try { sessionStorage.removeItem(LAST_SAVED_KEY); } catch (e) {}
}

function toggleElement(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const isHidden = (el.style.display === 'none' || el.style.display === '');
    el.style.display = isHidden ? (el.tagName === 'TR' ? 'table-row' : 'block') : 'none';
}

function filterMenuItems(category, btn) {
    document.querySelectorAll('.menu-filter-btn').forEach(b => {
        b.style.background = '#fff';
        b.style.color = '#141210';
    });
    btn.style.background = 'var(--bt-espresso)';
    btn.style.color = '#fff';

    document.querySelectorAll('.menu-item-row').forEach(row => {
        const aud = row.getAttribute('data-audience');
        const type = row.getAttribute('data-type');
        if (category === 'all') {
            row.style.display = '';
        } else if (category === 'custom') {
            row.style.display = type === 'custom' ? '' : 'none';
        } else if (category === 'guest') {
            row.style.display = (aud === 'guest' || aud === 'all') ? '' : 'none';
        } else {
            row.style.display = aud === category ? '' : 'none';
        }
    });
}

// Intercept form submissions across console sections so the saved feature never returns to collapsed
document.addEventListener('submit', function (e) {
    const form = e.target;
    const sectionEl = form.closest('section');
    if (sectionEl && ALL_CONSOLE_SECTIONS.includes(sectionEl.id)) {
        const secId = sectionEl.id;
        try {
            sessionStorage.setItem(LAST_SAVED_KEY, secId);
            let saved = getSavedExpandedSections();
            if (!saved.includes(secId)) {
                saved.push(secId);
                saveExpandedSections(saved);
            }
        } catch (err) {}
    }
});

// Restore expanded state and position after saving
document.addEventListener('DOMContentLoaded', function () {
    const savedExpanded = getSavedExpandedSections();
    let lastSaved = null;
    try {
        lastSaved = sessionStorage.getItem(LAST_SAVED_KEY);
    } catch (e) {}

    if (lastSaved && ALL_CONSOLE_SECTIONS.includes(lastSaved)) {
        if (!savedExpanded.includes(lastSaved)) {
            savedExpanded.push(lastSaved);
            saveExpandedSections(savedExpanded);
        }
    }

    const hash = window.location.hash ? window.location.hash.substring(1) : null;
    if (hash && ALL_CONSOLE_SECTIONS.includes(hash)) {
        if (!savedExpanded.includes(hash)) {
            savedExpanded.push(hash);
            saveExpandedSections(savedExpanded);
        }
    }

    savedExpanded.forEach(function (sec) {
        if (ALL_CONSOLE_SECTIONS.includes(sec)) {
            setSectionState(sec, true);
        }
    });

    const targetSec = lastSaved || (hash && ALL_CONSOLE_SECTIONS.includes(hash) ? hash : null);
    if (targetSec) {
        const el = document.getElementById(targetSec);
        if (el) {
            setTimeout(function () {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 80);
        }
        try {
            sessionStorage.removeItem(LAST_SAVED_KEY);
        } catch (e) {}
    }
});
</script>
@endsection
