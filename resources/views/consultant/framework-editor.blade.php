@extends('layouts.app')

@section('title', 'Configure Diagnostic Framework & Methodology')

@push('head')
<style>
    .fw-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(20, 18, 16, 0.82);
        z-index: 10050;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .fw-modal-backdrop.open {
        display: flex;
    }
    .fw-modal-box {
        background-color: var(--bt-white);
        border: 2px solid var(--bt-ink);
        border-top: 5px solid var(--bt-amber);
        max-width: 640px;
        width: 100%;
        padding: 20px;
    }
    .fw-choice-card {
        border: 2px solid var(--bt-border);
        background-color: var(--bt-cream);
        padding: 12px 14px;
        margin-bottom: 10px;
        cursor: pointer;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .fw-choice-card.selected {
        border-color: var(--bt-ink);
        background-color: var(--bt-amber-pale);
    }
</style>
@endpush

@section('content')
<section class="sec-espresso">
    <div class="bt-container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
            <div>
                <div class="eyebrow">CONFIGURE · BRIDGE-TAILOR DIAGNOSTIC METHODOLOGY ENGINE</div>
                <h1 style="font-size:28px;color:#fff;">Edit Questions, Symptom Weightings, Programmes &amp; Insight Rules</h1>
                <p style="font-size:13px;color:#d6cdc2;margin:4px 0 0;">
                    When saving changes to the Framework JSON, you will be prompted whether it is a <strong>Mere Text / Wording Edit</strong> (updates current version in-place) or a <strong>New Framework Version</strong> (archives and logs the previous version so it can be referenced or restored at any time).
                </p>
            </div>
            @if($inspectedRecord && $activeRecord && $inspectedRecord->id !== $activeRecord->id)
                <a href="{{ route('consultant.framework') }}" class="btn btn-amber btn-sm">
                    ← Return to Active Version (v{{ $activeRecord->version }})
                </a>
            @endif
        </div>
    </div>
</section>

<section class="sec-cream">
    <div class="bt-container">
        <div class="flat-grid-3" style="margin-bottom:14px;">
            <div class="flat-box">
                <div class="eyebrow">CURRENTLY ACTIVE ENGINE VERSION</div>
                <div style="font-size:24px;font-weight:800;">v{{ $activeFw->version() }}</div>
                <div style="font-size:12px;color:#555;">{{ $activeFw->get('name') }}</div>
                @if($activeRecord?->change_notes)
                    <div style="font-size:11px;color:var(--bt-amber-dark);margin-top:4px;font-weight:600;">
                        Latest Note: {{ $activeRecord->change_notes }}
                    </div>
                @endif
            </div>

            <div class="flat-box">
                <div class="eyebrow">LOADED IN EDITOR ({{ $inspectedRecord && $activeRecord && $inspectedRecord->id !== $activeRecord->id ? 'ARCHIVED SNAPSHOT' : 'ACTIVE' }})</div>
                <div style="font-size:12.5px;line-height:1.6;">
                    <div><strong>Loaded Version:</strong> <span class="tag {{ $inspectedRecord?->is_active ? 'tag-ok' : 'tag-medium' }}">v{{ $fw->version() }}</span></div>
                    <div><strong>Modules:</strong> {{ count($fw->modules()) }} ({{ implode(', ', $fw->moduleKeys()) }})</div>
                    <div><strong>Criteria:</strong> {{ count($fw->criteria()) }} · <strong>Questions:</strong> {{ count($fw->allQuestions()) }} · <strong>Symptoms:</strong> {{ count($fw->symptoms()) }}</div>
                </div>
            </div>

            <div class="flat-box">
                <div class="eyebrow">VERSION ARCHIVE SUMMARY</div>
                <div style="font-size:12.5px;line-height:1.6;">
                    <div><strong>Total Logged Versions:</strong> {{ $versions->count() }}</div>
                    <div><strong>Next Suggested Version:</strong> <code>v{{ $nextSuggestedVersion }}</code></div>
                    <div style="font-size:11px;color:#555;margin-top:3px;">
                        Click any archived version in the table below to load its JSON into the editor or restore it as active.
                    </div>
                </div>
            </div>
        </div>

        <!-- Framework JSON Editor Form -->
        <div class="flat-box" style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
                <div>
                    <div class="eyebrow">FRAMEWORK DEFINITION JSON EDITOR</div>
                    <h3 style="font-size:16px;">
                        Editing Definition from v{{ $fw->version() }} — {{ $fw->get('name') }}
                        @if($inspectedRecord && !$inspectedRecord->is_active)
                            <span class="tag tag-amber" style="margin-left:6px;">VIEWING ARCHIVED VERSION v{{ $inspectedRecord->version }}</span>
                        @endif
                    </h3>
                </div>
                <span id="json-change-badge" class="tag tag-solid">NO UNSAVED JSON EDITS YET</span>
            </div>

            <form id="framework-publish-form" action="{{ route('consultant.framework.publish') }}" method="POST">
                @csrf
                <input type="hidden" name="save_mode" id="f_save_mode" value="minor_edit">
                <input type="hidden" name="change_notes" id="f_change_notes" value="">

                <div class="flat-grid-2">
                    <div class="field">
                        <label>Version Number *</label>
                        <input type="text" name="version" id="f_version" required value="{{ old('version', $activeFw->version()) }}">
                    </div>
                    <div class="field">
                        <label>Framework Name *</label>
                        <input type="text" name="name" id="f_name" required value="{{ old('name', $fw->get('name')) }}">
                    </div>
                </div>

                <div class="field">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label style="margin-bottom:0;">Framework Definition JSON (Modules, Criteria, 3-Option Gateway Questions, Symptom Weights, Programmes &amp; Cross-Area Insight Rules)</label>
                    </div>

                    <!-- Interactive Keyword Search & Jump Toolbar -->
                    <div class="fw-search-panel" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;margin-bottom:10px;">
                        <!-- Row 1: Search, Match Status, Nav Arrows & Theme -->
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
                            <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:280px;">
                                <div style="position:relative;flex:1;">
                                    <input type="text" id="json-search-input" placeholder="🔍 Search keywords, module names, question codes (e.g. Strategy, FH, fc1)..."
                                           oninput="performSearch()" onkeydown="handleSearchKeyDown(event)"
                                           style="width:100%;padding:8px 12px 8px 32px;font-size:13px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;outline:none;">
                                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8;pointer-events:none;">🔍</span>
                                </div>
                                <span id="search-status" class="tag tag-solid" style="padding:6px 10px;border-radius:6px;font-size:11px;white-space:nowrap;">
                                    Type to search
                                </span>
                                <div style="display:flex;gap:4px;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="findPrevMatch()" title="Previous Match (Shift+Enter)">↑ Prev</button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="findNextMatch()" title="Next Match (Enter)">↓ Next</button>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:6px;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="toggleReplacePanel()" id="btn-toggle-replace">
                                    ⇄ Replace
                                </button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="toggleEditorTheme()" title="Switch Light/Dark Editor Canvas">
                                    🌓 Canvas Theme
                                </button>
                            </div>
                        </div>

                        <!-- Row 2: Replace Panel (Collapsible) -->
                        <div id="replace-panel" style="display:none;padding:10px 12px;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:10px;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;">Replace With:</span>
                            <input type="text" id="json-replace-input" placeholder="Replacement text..." style="flex:1;min-width:180px;padding:6px 10px;font-size:12.5px;border:1px solid #cbd5e1;border-radius:6px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="replaceCurrentMatch()">Replace</button>
                            <button type="button" class="btn btn-amber btn-sm" onclick="replaceAllMatches()">Replace All</button>
                        </div>

                        <!-- Row 3: Quick Jump Chips -->
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <span style="font-size:10.5px;font-weight:800;color:var(--bt-amber-deep);text-transform:uppercase;margin-right:2px;">Quick Jumps:</span>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;modules&quot;:')">🏢 Modules</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;SG&quot;:')">1. SG Strategy</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;FH&quot;:')">2. FH Finance</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;OE&quot;:')">3. OE Operations</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;MG&quot;:')">4. MG Market</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;HC&quot;:')">5. HC Human Capital</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;symptoms&quot;:')">🚩 Symptoms</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;programmes&quot;:')">🎯 Programmes</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 8px;font-size:10.5px;border-radius:6px;" onclick="jumpToSection('&quot;scoring&quot;:')">📊 Scoring</button>
                        </div>
                    </div>

                    <textarea name="definition_json" id="f_definition_json" rows="24" oninput="onJsonEdited()" style="font-family:Consolas, Monaco, 'Courier New', monospace;font-size:12px;line-height:1.45;background:#1e293b;color:#f8fafc;border:1.5px solid #334155;border-radius:12px;padding:14px;">{{ old('definition_json', json_encode($fw->definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                    <span style="font-size:11.5px;color:#555;">
                        Structural validator checks all module keys, programme links, and ensures every evidence question has 3 ordered options.
                    </span>
                    <button type="button" class="btn btn-amber" onclick="openSavePromptModal()">
                        Validate &amp; Save Framework Changes →
                    </button>
                </div>
            </form>
        </div>

        <!-- Prompt Modal: Mere Text Edit vs. New Version -->
        <div class="fw-modal-backdrop" id="fw-save-modal">
            <div class="fw-modal-box">
                <div class="eyebrow">FRAMEWORK CHANGE CLASSIFICATION PROMPT</div>
                <h2 style="font-size:21px;margin-bottom:6px;">How Should This JSON Change Be Saved?</h2>
                <p style="font-size:12.5px;color:#443e3a;margin:0 0 12px;">
                    Specify whether your edit is a <strong>minor text/wording correction</strong> to the current active version or a <strong>new framework version</strong> (which archives and logs <code>v{{ $activeFw->version() }}</code> for permanent reference).
                </p>

                <!-- Option 1: Mere Edit of Text -->
                <label class="fw-choice-card selected" id="choice-card-minor" onclick="selectSaveMode('minor_edit')">
                    <input type="radio" name="modal_mode_choice" value="minor_edit" checked style="margin-top:3px;">
                    <div>
                        <div style="font-weight:800;font-size:13px;">Option A · Mere Edit of Text / Wording (In-Place Update)</div>
                        <div style="font-size:11.5px;color:#443e3a;margin-top:2px;">
                            Updates wording, typos, or labels directly inside the current active version (<strong>v{{ $activeFw->version() }}</strong>) without creating a new version number.
                        </div>
                    </div>
                </label>

                <!-- Option 2: Publish as New Version & Log Previous Version -->
                <label class="fw-choice-card" id="choice-card-new" onclick="selectSaveMode('new_version')">
                    <input type="radio" name="modal_mode_choice" value="new_version" style="margin-top:3px;">
                    <div style="flex:1;">
                        <div style="font-weight:800;font-size:13px;">Option B · Publish as a New Version &amp; Archive Previous Version</div>
                        <div style="font-size:11.5px;color:#443e3a;margin-top:2px;">
                            Preserves <strong>v{{ $activeFw->version() }}</strong> in the version log so it can be referenced or restored at any time, and publishes this JSON as a new active version:
                        </div>
                        <div id="modal-new-version-box" style="margin-top:8px;display:none;">
                            <label style="display:block;font-size:10px;font-weight:800;text-transform:uppercase;color:var(--bt-umber);margin-bottom:3px;">New Version Number *</label>
                            <input type="text" id="modal_new_version_input" value="{{ $nextSuggestedVersion }}" style="width:180px;border:1px solid var(--bt-ink);padding:5px 8px;font-weight:800;font-size:13px;background:#fff;">
                        </div>
                    </div>
                </label>

                <div class="field" style="margin-top:10px;">
                    <label>Change Summary / Audit Note (Logged in Version History)</label>
                    <input type="text" id="modal_change_notes_input" placeholder="e.g. Refined FH2 cash-flow wording or added new agritech criterion...">
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:14px;padding-top:10px;border-top:1px solid var(--bt-border);">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeSavePromptModal()">Cancel</button>
                    <button type="button" class="btn btn-amber" onclick="confirmAndSubmitFramework()">Confirm &amp; Save Framework →</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Solid White Band — Logged Framework Versions Archive & Reference Table -->
<section class="sec-white" id="version-archive">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">PERMANENT VERSION ARCHIVE &amp; REFERENCE LOG</div>
                <h2 style="font-size:23px;">Logged Framework Versions (Inspect JSON or Restore Any Previous Version)</h2>
            </div>
        </div>

        <table class="bt-table" style="margin-bottom:16px;">
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Framework Name</th>
                    <th>Change Type &amp; Notes</th>
                    <th>Published By &amp; Timestamp</th>
                    <th>Pinned Assessments</th>
                    <th>Status</th>
                    <th>Reference / Restore Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($versions as $v)
                    @php
                        $vFw = $v->toFramework();
                    @endphp
                    <tr style="{{ $inspectedRecord && $inspectedRecord->id === $v->id ? 'outline:2px solid var(--bt-amber);' : '' }}">
                        <td style="font-weight:800;font-size:14px;">v{{ $v->version }}</td>
                        <td>
                            <div style="font-weight:700;">{{ $v->name }}</div>
                            <div style="font-size:11px;color:#666;">
                                {{ count($vFw->modules()) }} modules · {{ count($vFw->allQuestions()) }} questions · {{ count($vFw->symptoms()) }} symptoms
                            </div>
                        </td>
                        <td style="font-size:11.5px;">
                            <span class="tag {{ $v->change_type === 'minor_edit' ? 'tag-solid' : 'tag-amber' }}">
                                {{ $v->change_type === 'minor_edit' ? 'MINOR TEXT EDIT' : 'NEW VERSION' }}
                            </span>
                            <div style="margin-top:3px;color:#333;">{{ $v->change_notes ?: 'Initial seeded framework definition.' }}</div>
                        </td>
                        <td style="font-size:11.5px;">
                            <div><strong>{{ $v->publisher?->name ?? 'System Seeder' }}</strong></div>
                            <div style="color:#666;">{{ $v->published_at?->format('d M Y, H:i') ?? $v->created_at?->format('d M Y, H:i') }}</div>
                        </td>
                        <td style="font-weight:700;">{{ $v->assessments_count }} assessment(s)</td>
                        <td>
                            <span class="tag {{ $v->is_active ? 'tag-ok' : 'tag-medium' }}">
                                {{ $v->is_active ? 'ACTIVE ENGINE' : 'ARCHIVED LOG' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                <a href="{{ route('consultant.framework', ['inspect' => $v->id]) }}" class="btn btn-outline btn-sm">
                                    {{ $inspectedRecord && $inspectedRecord->id === $v->id ? 'Loaded in Editor' : 'Load / Inspect JSON' }}
                                </a>
                                @if(!$v->is_active)
                                    <form action="{{ route('consultant.framework.activate', $v->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn btn-amber btn-sm">Set Active</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Immutable Audit Trail of Framework Changes -->
        @if($auditHistory->count())
            <div class="eyebrow" style="margin-bottom:6px;">FRAMEWORK AUDIT LOG (ALL TEXT EDITS, NEW VERSIONS &amp; ACTIVATIONS)</div>
            <table class="bt-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Event</th>
                        <th>Actor</th>
                        <th>Logged Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditHistory as $log)
                        <tr>
                            <td style="font-size:11px;white-space:nowrap;">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td><code style="font-weight:700;color:var(--bt-amber-dark);">{{ $log->event }}</code></td>
                            <td>{{ $log->actor_label }}</td>
                            <td style="font-size:11px;"><code>{{ json_encode($log->metadata) }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
    const ACTIVE_VERSION = @json($activeFw->version());
    const NEXT_SUGGESTED_VERSION = @json($nextSuggestedVersion);
    let selectedMode = 'minor_edit';

    // Search & Keyword Navigation State
    let searchMatches = [];
    let currentMatchIndex = -1;

    function performSearch() {
        const query = document.getElementById('json-search-input').value;
        const textarea = document.getElementById('f_definition_json');
        const text = textarea.value;
        searchMatches = [];
        currentMatchIndex = -1;

        if (!query || query.trim() === '') {
            updateSearchUI();
            return;
        }

        const lowerQuery = query.toLowerCase();
        const lowerText = text.toLowerCase();
        let pos = lowerText.indexOf(lowerQuery);

        while (pos !== -1) {
            searchMatches.push({ start: pos, end: pos + query.length });
            pos = lowerText.indexOf(lowerQuery, pos + 1);
        }

        if (searchMatches.length > 0) {
            currentMatchIndex = 0;
            highlightMatch(currentMatchIndex);
        } else {
            updateSearchUI();
        }
    }

    function highlightMatch(index) {
        if (index < 0 || index >= searchMatches.length) return;
        const textarea = document.getElementById('f_definition_json');
        const match = searchMatches[index];

        textarea.focus();
        textarea.setSelectionRange(match.start, match.end);

        // Compute exact line number
        const textUpToMatch = textarea.value.substring(0, match.start);
        const lineNum = textUpToMatch.split('\n').length;

        // Smoothly scroll to the matched position in the textarea
        const totalLines = textarea.value.split('\n').length;
        const approxLineHeight = textarea.scrollHeight / Math.max(totalLines, 1);
        textarea.scrollTop = Math.max(0, (lineNum * approxLineHeight) - 160);

        updateSearchUI(lineNum);
    }

    function findNextMatch() {
        if (searchMatches.length === 0) {
            performSearch();
            return;
        }
        currentMatchIndex = (currentMatchIndex + 1) % searchMatches.length;
        highlightMatch(currentMatchIndex);
    }

    function findPrevMatch() {
        if (searchMatches.length === 0) {
            performSearch();
            return;
        }
        currentMatchIndex = (currentMatchIndex - 1 + searchMatches.length) % searchMatches.length;
        highlightMatch(currentMatchIndex);
    }

    function handleSearchKeyDown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (e.shiftKey) {
                findPrevMatch();
            } else {
                findNextMatch();
            }
        }
    }

    function jumpToSection(keyword) {
        const searchInput = document.getElementById('json-search-input');
        searchInput.value = keyword;
        performSearch();
    }

    function updateSearchUI(lineNum = null) {
        const statusEl = document.getElementById('search-status');
        const query = document.getElementById('json-search-input').value;
        if (!query || query.trim() === '') {
            statusEl.textContent = 'Type to search';
            statusEl.className = 'tag tag-solid';
            return;
        }
        if (searchMatches.length === 0) {
            statusEl.textContent = '0 matches';
            statusEl.className = 'tag tag-critical';
        } else {
            const lineText = lineNum ? ` · Line ${lineNum}` : '';
            statusEl.textContent = `${currentMatchIndex + 1} of ${searchMatches.length}${lineText}`;
            statusEl.className = 'tag tag-amber';
        }
    }

    function toggleReplacePanel() {
        const panel = document.getElementById('replace-panel');
        const btn = document.getElementById('btn-toggle-replace');
        const isOpen = panel.style.display === 'flex';
        panel.style.display = isOpen ? 'none' : 'flex';
        btn.classList.toggle('btn-amber', !isOpen);
    }

    function replaceCurrentMatch() {
        if (searchMatches.length === 0 || currentMatchIndex === -1) return;
        const textarea = document.getElementById('f_definition_json');
        const replaceText = document.getElementById('json-replace-input').value;
        const match = searchMatches[currentMatchIndex];

        const before = textarea.value.substring(0, match.start);
        const after = textarea.value.substring(match.end);
        textarea.value = before + replaceText + after;

        onJsonEdited();
        performSearch();
    }

    function replaceAllMatches() {
        const query = document.getElementById('json-search-input').value;
        const replaceText = document.getElementById('json-replace-input').value;
        if (!query) return;

        const count = searchMatches.length;
        if (count === 0) {
            alert('No matches found to replace.');
            return;
        }

        if (!confirm(`Replace all ${count} occurrences of "${query}" with "${replaceText}"?`)) {
            return;
        }

        const textarea = document.getElementById('f_definition_json');
        textarea.value = textarea.value.split(query).join(replaceText);
        onJsonEdited();
        performSearch();
    }

    function toggleEditorTheme() {
        const textarea = document.getElementById('f_definition_json');
        if (textarea.classList.contains('light-theme')) {
            textarea.classList.remove('light-theme');
            textarea.style.backgroundColor = '#1e293b';
            textarea.style.color = '#f8fafc';
            textarea.style.borderColor = '#334155';
        } else {
            textarea.classList.add('light-theme');
            textarea.style.backgroundColor = '#ffffff';
            textarea.style.color = '#0f172a';
            textarea.style.borderColor = '#cbd5e1';
        }
    }

    function onJsonEdited() {
        const badge = document.getElementById('json-change-badge');
        if (badge) {
            badge.className = 'tag tag-amber';
            badge.textContent = 'UNSAVED JSON CHANGES DETECTED — CLICK SAVE TO CLASSIFY';
        }
    }

    function openSavePromptModal() {
        const rawJson = document.getElementById('f_definition_json').value;
        try {
            JSON.parse(rawJson);
        } catch (err) {
            alert('Invalid JSON syntax in editor: ' + err.message);
            return;
        }
        const currentVerInput = document.getElementById('f_version').value.trim();
        if (currentVerInput && currentVerInput !== ACTIVE_VERSION) {
            document.getElementById('modal_new_version_input').value = currentVerInput;
            selectSaveMode('new_version');
        } else {
            selectSaveMode('minor_edit');
        }
        document.getElementById('fw-save-modal').classList.add('open');
    }

    function closeSavePromptModal() {
        document.getElementById('fw-save-modal').classList.remove('open');
    }

    function selectSaveMode(mode) {
        selectedMode = mode;
        document.getElementById('f_save_mode').value = mode;
        const minorCard = document.getElementById('choice-card-minor');
        const newCard = document.getElementById('choice-card-new');
        const newVerBox = document.getElementById('modal-new-version-box');

        minorCard.classList.toggle('selected', mode === 'minor_edit');
        newCard.classList.toggle('selected', mode === 'new_version');
        minorCard.querySelector('input[type="radio"]').checked = (mode === 'minor_edit');
        newCard.querySelector('input[type="radio"]').checked = (mode === 'new_version');
        newVerBox.style.display = (mode === 'new_version') ? 'block' : 'none';
    }

    function confirmAndSubmitFramework() {
        const notes = document.getElementById('modal_change_notes_input').value.trim();
        document.getElementById('f_change_notes').value = notes;

        if (selectedMode === 'new_version') {
            const newVer = document.getElementById('modal_new_version_input').value.trim() || NEXT_SUGGESTED_VERSION;
            document.getElementById('f_version').value = newVer;
        } else {
            document.getElementById('f_version').value = ACTIVE_VERSION;
        }

        document.getElementById('framework-publish-form').submit();
    }
</script>
@endpush
