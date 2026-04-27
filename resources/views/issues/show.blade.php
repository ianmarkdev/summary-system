@extends('layouts.app')

@section('title', 'Issue #' . $issue->id)

@section('content')

@if($issue->escalated)
<div class="escalation-banner">
    &#9888; This issue has been flagged for escalation
    @if($issue->escalated_at)
        <span class="text-muted" style="font-weight:400">&mdash; {{ $issue->escalated_at->format('M j, Y H:i') }}</span>
    @endif
</div>
@endif

<div class="flex-between" style="margin-bottom:1.25rem">
    <div>
        <div class="text-muted" style="font-size:.85rem;margin-bottom:.25rem">
            <a href="{{ route('issues.index') }}">Issues</a> / #{{ $issue->id }}
        </div>
        <h1 style="font-size:1.4rem;font-weight:700">{{ $issue->title }}</h1>
    </div>
    <div style="display:flex;gap:.5rem">
        <a href="{{ route('issues.edit', $issue) }}" class="btn btn-secondary">Edit</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem">

    {{-- Main column --}}
    <div>
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header"><strong>Description</strong></div>
            <div class="card-body" style="white-space:pre-wrap;font-size:.95rem">{{ $issue->description }}</div>
        </div>

        <div class="ai-box">
            <div class="ai-box-title">&#10024; AI Summary</div>
            <p style="font-size:.95rem">{{ $issue->summary ?? 'No summary generated yet.' }}</p>

            <div class="ai-box-title" style="margin-top:1rem">&#128204; Suggested Next Action</div>
            <p style="font-size:.95rem">{{ $issue->next_action ?? 'No suggestion available.' }}</p>

            @if($issue->summary_source)
                <div class="ai-source">
                    Source: {{ $issue->summary_source === 'claude_api' ? '&#127775; Claude API' : '&#9881; Rules-based fallback' }}
                </div>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="card">
            <div class="card-header"><strong>Details</strong></div>
            <div class="card-body">
                <div class="detail-grid" style="grid-template-columns:1fr">

                    <div>
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="badge {{ $issue->statusBadgeClass() }}">{{ str_replace('_',' ',$issue->status) }}</span>
                        </div>
                    </div>

                    <div class="mt-1">
                        <div class="detail-label">Priority</div>
                        <div class="detail-value">
                            <span class="badge {{ $issue->priorityBadgeClass() }}">{{ $issue->priority }}</span>
                        </div>
                    </div>

                    <div class="mt-1">
                        <div class="detail-label">Category</div>
                        <div class="detail-value">{{ ucfirst($issue->category) }}</div>
                    </div>

                    <div class="mt-1">
                        <div class="detail-label">Escalated</div>
                        <div class="detail-value">
                            @if($issue->escalated)
                                <span class="badge badge-escalated">Yes</span>
                            @else
                                <span style="color:var(--muted)">No</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-1">
                        <div class="detail-label">Created</div>
                        <div class="detail-value">{{ $issue->created_at->format('M j, Y H:i') }}</div>
                    </div>

                    <div class="mt-1">
                        <div class="detail-label">Updated</div>
                        <div class="detail-value">{{ $issue->updated_at->format('M j, Y H:i') }}</div>
                    </div>
                </div>

                <div class="mt-2">
                    <a href="{{ route('issues.edit', $issue) }}" class="btn btn-primary" style="width:100%;justify-content:center">Edit Issue</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
