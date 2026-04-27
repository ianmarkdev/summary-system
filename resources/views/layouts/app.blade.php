<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Issue Tracker') – Issue Intake System</title>
    <style>
        /* ------------------------------------------------------------------ */
        /* Reset / base                                                        */
        /* ------------------------------------------------------------------ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #f8fafc;
            --surface:   #ffffff;
            --border:    #e2e8f0;
            --text:      #1e293b;
            --muted:     #64748b;
            --accent:    #3b82f6;
            --accent-dk: #2563eb;

            /* priority colours */
            --c-low:      #22c55e;
            --c-medium:   #f59e0b;
            --c-high:     #f97316;
            --c-critical: #ef4444;

            /* status colours */
            --c-open:        #3b82f6;
            --c-in-progress: #a855f7;
            --c-resolved:    #22c55e;
            --c-closed:      #94a3b8;
        }

        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); line-height: 1.6; }

        a { color: var(--accent); text-decoration: none; }
        a:hover { color: var(--accent-dk); text-decoration: underline; }

        /* ------------------------------------------------------------------ */
        /* Layout                                                             */
        /* ------------------------------------------------------------------ */
        .nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            height: 56px;
        }
        .nav-brand { font-weight: 700; font-size: 1.1rem; color: var(--text); }
        .nav-brand:hover { text-decoration: none; }
        .nav-link { color: var(--muted); font-size: .9rem; }
        .nav-link:hover { color: var(--text); text-decoration: none; }
        .nav-spacer { flex: 1; }

        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }

        /* ------------------------------------------------------------------ */
        /* Alerts                                                             */
        /* ------------------------------------------------------------------ */
        .alert { padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: .9rem; }
        .alert-success { background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error   { background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; }

        /* ------------------------------------------------------------------ */
        /* Card                                                               */
        /* ------------------------------------------------------------------ */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: .75rem; }
        .card-body   { padding: 1.25rem; }

        /* ------------------------------------------------------------------ */
        /* Badges                                                             */
        /* ------------------------------------------------------------------ */
        .badge {
            display: inline-block;
            padding: .2rem .65rem;
            border-radius: 9999px;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .badge-low       { background: #dcfce7; color: #15803d; }
        .badge-medium    { background: #fef3c7; color: #92400e; }
        .badge-high      { background: #ffedd5; color: #c2410c; }
        .badge-critical  { background: #fee2e2; color: #b91c1c; }
        .badge-open        { background: #dbeafe; color: #1d4ed8; }
        .badge-in-progress { background: #f3e8ff; color: #7e22ce; }
        .badge-resolved    { background: #dcfce7; color: #15803d; }
        .badge-closed      { background: #f1f5f9; color: #475569; }
        .badge-escalated   { background: #fee2e2; color: #b91c1c; }

        /* ------------------------------------------------------------------ */
        /* Table                                                              */
        /* ------------------------------------------------------------------ */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { padding: .65rem 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { font-weight: 600; color: var(--muted); font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; background: var(--bg); }
        tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }

        /* ------------------------------------------------------------------ */
        /* Form elements                                                      */
        /* ------------------------------------------------------------------ */
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: var(--text); }
        input[type=text],
        textarea,
        select {
            width: 100%;
            padding: .55rem .8rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: .95rem;
            font-family: inherit;
            background: var(--surface);
            color: var(--text);
            transition: border-color .15s;
        }
        input[type=text]:focus,
        textarea:focus,
        select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .form-error { color: #dc2626; font-size: .8rem; margin-top: .3rem; }

        /* ------------------------------------------------------------------ */
        /* Buttons                                                            */
        /* ------------------------------------------------------------------ */
        .btn {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .5rem 1.1rem;
            border: none; border-radius: 6px;
            font-size: .9rem; font-weight: 600; cursor: pointer;
            text-decoration: none; transition: background .15s, opacity .15s;
        }
        .btn-primary   { background: var(--accent);    color: #fff; }
        .btn-primary:hover { background: var(--accent-dk); text-decoration: none; color: #fff; }
        .btn-secondary { background: var(--bg); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); text-decoration: none; }
        .btn-sm { padding: .3rem .75rem; font-size: .8rem; }

        /* ------------------------------------------------------------------ */
        /* Filter bar                                                         */
        /* ------------------------------------------------------------------ */
        .filter-bar {
            display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end;
            padding: 1rem 1.25rem;
            background: var(--bg);
            border-bottom: 1px solid var(--border);
        }
        .filter-bar .form-group { margin: 0; min-width: 140px; }
        .filter-bar label { font-size: .78rem; }
        .filter-bar select { padding: .35rem .6rem; font-size: .85rem; }

        /* ------------------------------------------------------------------ */
        /* Detail page                                                        */
        /* ------------------------------------------------------------------ */
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media(max-width: 640px) { .detail-grid { grid-template-columns: 1fr; } }
        .detail-label { font-size: .8rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
        .detail-value { margin-top: .2rem; }

        .ai-box {
            background: linear-gradient(135deg, #eff6ff, #f0fdf4);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 1rem 1.25rem;
        }
        .ai-box-title { font-size: .8rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: .35rem; }
        .ai-source    { font-size: .72rem; color: var(--muted); margin-top: .5rem; }

        .escalation-banner {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: .75rem 1rem;
            display: flex; align-items: center; gap: .5rem;
            margin-bottom: 1.25rem;
            font-size: .9rem; font-weight: 600; color: #991b1b;
        }

        /* ------------------------------------------------------------------ */
        /* Pagination                                                         */
        /* ------------------------------------------------------------------ */
        .pagination { display: flex; gap: .35rem; flex-wrap: wrap; margin-top: 1.25rem; }
        .pagination .page-link,
        .pagination span {
            padding: .35rem .75rem;
            border-radius: 5px;
            font-size: .85rem;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
        }
        .pagination .page-link:hover { background: var(--border); text-decoration: none; }
        .pagination .active span { background: var(--accent); color: #fff; border-color: var(--accent); }
        .pagination .disabled span { opacity: .45; cursor: not-allowed; }

        /* ------------------------------------------------------------------ */
        /* Misc                                                               */
        /* ------------------------------------------------------------------ */
        .page-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: .75rem; }
        .text-muted  { color: var(--muted); }
        .mt-1 { margin-top: .5rem; }
        .mt-2 { margin-top: 1rem; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; }
    </style>
</head>
<body>

<nav class="nav">
    <a href="{{ route('issues.index') }}" class="nav-brand">&#9889; Issue Tracker</a>
    <a href="{{ route('issues.index') }}" class="nav-link">All Issues</a>
    <a href="{{ route('issues.index', ['escalated' => 1]) }}" class="nav-link">Escalated</a>
    <span class="nav-spacer"></span>
    <a href="{{ route('issues.create') }}" class="btn btn-primary btn-sm">+ New Issue</a>
</nav>

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @yield('content')
</div>

</body>
</html>
