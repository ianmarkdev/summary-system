@extends('layouts.app')

@section('title', 'Edit Issue #' . $issue->id)

@section('content')
<div style="max-width:720px;margin:0 auto">
    <div class="text-muted" style="font-size:.85rem;margin-bottom:.5rem">
        <a href="{{ route('issues.index') }}">Issues</a> /
        <a href="{{ route('issues.show', $issue) }}">#{{ $issue->id }}</a> / Edit
    </div>
    <h1 class="page-title">Edit Issue #{{ $issue->id }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('issues.update', $issue) }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $issue->title) }}" required>
                    @error('title')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="6" required>{{ old('description', $issue->description) }}</textarea>
                    @error('description')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="detail-grid">
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            @foreach($priorities as $p)
                                <option value="{{ $p }}" @selected(old('priority', $issue->priority) === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            @foreach($categories as $c)
                                <option value="{{ $c }}" @selected(old('category', $issue->category) === $c)>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected(old('status', $issue->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:.5rem">
                    <a href="{{ route('issues.show', $issue) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
