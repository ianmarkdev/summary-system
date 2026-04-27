@extends('layouts.app')

@section('title', 'All Issues')

@section('content')
<div class="flex-between">
    <h1 class="page-title" style="margin-bottom:0">All Issues</h1>
    <a href="{{ route('issues.create') }}" class="btn btn-primary">+ New Issue</a>
</div>

{{-- Filter bar --}}
<div class="card mt-2">
    <form method="GET" action="{{ route('issues.index') }}" class="filter-bar">
        <div class="form-group">
            <label>Status</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Priority</label>
            <select name="priority" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                @foreach($priorities as $p)
                    <option value="{{ $p }}" @selected(($filters['priority'] ?? '') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Category</label>
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c }}" @selected(($filters['category'] ?? '') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Escalated</label>
            <select name="escalated" onchange="this.form.submit()">
                <option value="">Any</option>
                <option value="1" @selected(!empty($filters['escalated']))>Yes – escalated only</option>
            </select>
        </div>

        @if(array_filter($filters))
            <div class="form-group">
                <label>&nbsp;</label>
                <a href="{{ route('issues.index') }}" class="btn btn-secondary">Clear filters</a>
            </div>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Summary</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($issues as $issue)
                <tr>
                    <td class="text-muted">{{ $issue->id }}</td>
                    <td>
                        <a href="{{ route('issues.show', $issue) }}">{{ $issue->title }}</a>
                        @if($issue->escalated)
                            <span class="badge badge-escalated" style="margin-left:.4rem">&#9888; escalated</span>
                        @endif
                    </td>
                    <td>{{ ucfirst($issue->category) }}</td>
                    <td><span class="badge {{ $issue->priorityBadgeClass() }}">{{ $issue->priority }}</span></td>
                    <td><span class="badge {{ $issue->statusBadgeClass() }}">{{ str_replace('_',' ',$issue->status) }}</span></td>
                    <td class="text-muted" style="max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $issue->summary ?? '—' }}
                    </td>
                    <td class="text-muted" style="white-space:nowrap">{{ $issue->created_at->diffForHumans() }}</td>
                    <td><a href="{{ route('issues.show', $issue) }}" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:2rem;color:var(--muted)">
                        No issues found. <a href="{{ route('issues.create') }}">Create the first one.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($issues->hasPages())
        <div style="padding:.75rem 1.25rem">
            {{ $issues->links('pagination::default') }}
        </div>
    @endif
</div>
@endsection
