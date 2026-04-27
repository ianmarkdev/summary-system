@extends('layouts.app')

@section('title', 'New Issue')

@section('content')
<div style="max-width:720px;margin:0 auto">
    <div class="text-muted" style="font-size:.85rem;margin-bottom:.5rem">
        <a href="{{ route('issues.index') }}">Issues</a> / New
    </div>
    <h1 class="page-title">Submit New Issue</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('issues.store') }}">
                @csrf

                <div class="form-group">
                    <label for="title">Title <span style="color:red">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}"
                           placeholder="Short, descriptive title of the issue" required>
                    @error('title')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="description">Description <span style="color:red">*</span></label>
                    <textarea id="description" name="description" rows="6"
                              placeholder="Describe the issue in detail – what happened, expected behaviour, impact…" required>{{ old('description') }}</textarea>
                    @error('description')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="detail-grid">
                    <div class="form-group">
                        <label for="priority">Priority <span style="color:red">*</span></label>
                        <select id="priority" name="priority" required>
                            @foreach($priorities as $p)
                                <option value="{{ $p }}" @selected(old('priority') === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                        @error('priority')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span style="color:red">*</span></label>
                        <select id="category" name="category" required>
                            @foreach($categories as $c)
                                <option value="{{ $c }}" @selected(old('category') === $c)>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                        @error('category')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:.5rem">
                    <a href="{{ route('issues.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
