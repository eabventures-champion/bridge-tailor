@extends('layouts.app')

@section('title', "Team Diagnostic Input — {$company->name}")

@section('content')
<section class="sec-espresso">
    <div class="bt-container">
        <div class="eyebrow">TEAM MULTI-RESPONDENT DIAGNOSTIC · {{ strtoupper($company->name) }}</div>
        <h1 style="font-size:28px;color:#fff;">Respondent Perspective: {{ $invite->respondent_name }} ({{ $invite->respondent_role }})</h1>
        <p style="font-size:13px;color:#d6cdc2;margin:6px 0 0;">
            Your responses are blended with leadership's answers to expose perception gaps across departments.
        </p>
    </div>
</section>

<section class="sec-cream">
    <div class="bt-container">
        @if($invite->status === 'submitted')
            <div class="flat-box" style="border-left:5px solid var(--bt-teal);margin-bottom:14px;">
                <span class="tag tag-ok">SUBMITTED {{ $invite->submitted_at?->format('d M Y H:i') }}</span>
                <h3 style="font-size:16px;margin-top:6px;">Your team contribution has been merged into {{ $company->name }}'s diagnostic</h3>
                <p style="font-size:12.5px;margin:4px 0 10px;">You can update your responses below if needed, or view the company diagnostic report.</p>
                <a href="{{ route('diagnostic.report', $assessment->ulid) }}" class="btn btn-sm">View Updated Diagnostic Report →</a>
            </div>
        @endif

        <form action="{{ route('diagnostic.team.submit', $invite->token) }}" method="POST">
            @csrf
            @foreach($fw->modules() as $mKey => $module)
                @if(in_array($mKey, $assessment->modules_in_scope ?? $fw->moduleKeys()))
                    <div class="flat-box" style="margin-bottom:12px;">
                        <div class="eyebrow">MODULE {{ $mKey }}</div>
                        <h3 style="font-size:16px;margin-bottom:8px;">{{ $module['name'] }}</h3>
                        <table class="bt-table">
                            <thead>
                                <tr>
                                    <th style="width:52%;">Diagnostic Evidence Question</th>
                                    <th style="width:48%;">Your Assessment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($module['criteria'] as $cKey => $crit)
                                    @foreach($crit['questions'] as $q)
                                        @if($fw->appliesToProfile($q, $company->stage, $company->sector))
                                            @php $curAns = $invite->answers[$q['code']] ?? ($assessment->answers[$q['code']] ?? 1); @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $q['text'] }}</strong>
                                                    <div style="font-size:10.5px;color:#666;">{{ $cKey }} · {{ $crit['name'] }}</div>
                                                </td>
                                                <td>
                                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                                        @foreach($q['options'] as $idx => $opt)
                                                            <label style="border:1px solid var(--bt-border);padding:5px 8px;background:#fff;cursor:pointer;font-size:11.5px;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                                                <input type="radio" name="answers[{{ $q['code'] }}]" value="{{ $idx }}" {{ (int)$curAns === $idx ? 'checked' : '' }}>
                                                                {{ $opt }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach

            <div class="flat-box" style="margin-bottom:14px;">
                <div class="field" style="margin-bottom:0;">
                    <label>Departmental Notes / Operational Bottlenecks You Want Flagged for the Bridge Tailor Consultant</label>
                    <textarea name="notes" rows="3" placeholder="Any specific risk or operational reality from your department's perspective...">{{ $invite->notes }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-amber">Submit Team Perspective &amp; Update Company Score →</button>
        </form>
    </div>
</section>
@endsection
