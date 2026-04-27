<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Services\IssueAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function __construct(private readonly IssueAIService $ai) {}

    // -------------------------------------------------------------------------
    // Web – list / show / create form
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $issues = $this->buildQuery($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('issues.index', [
            'issues'     => $issues,
            'priorities' => Issue::PRIORITIES,
            'categories' => Issue::CATEGORIES,
            'statuses'   => Issue::STATUSES,
            'filters'    => $request->only(['status', 'priority', 'category', 'escalated']),
        ]);
    }

    public function show(Issue $issue): View
    {
        return view('issues.show', compact('issue'));
    }

    public function create(): View
    {
        return view('issues.create', [
            'priorities' => Issue::PRIORITIES,
            'categories' => Issue::CATEGORIES,
            'statuses'   => Issue::STATUSES,
        ]);
    }

    public function store(StoreIssueRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $generated = $this->ai->generate(
            $data['title'],
            $data['description'],
            $data['priority'],
            $data['category']
        );

        $issue = Issue::create([
            ...$data,
            'summary'        => $generated['summary'],
            'next_action'    => $generated['next_action'],
            'summary_source' => $generated['source'],
        ]);

        if ($issue->needsEscalation()) {
            $issue->escalate();
        }

        return redirect()
            ->route('issues.show', $issue)
            ->with('success', 'Issue #' . $issue->id . ' created successfully.');
    }

    public function edit(Issue $issue): View
    {
        return view('issues.edit', [
            'issue'      => $issue,
            'priorities' => Issue::PRIORITIES,
            'categories' => Issue::CATEGORIES,
            'statuses'   => Issue::STATUSES,
        ]);
    }

    public function update(UpdateIssueRequest $request, Issue $issue): RedirectResponse
    {
        $data = $request->validated();
        $issue->update($data);

        // Re-generate summary if core fields changed
        if (array_intersect_key($data, array_flip(['title', 'description', 'priority', 'category']))) {
            $generated = $this->ai->generate(
                $issue->title,
                $issue->description,
                $issue->priority,
                $issue->category
            );
            $issue->update([
                'summary'        => $generated['summary'],
                'next_action'    => $generated['next_action'],
                'summary_source' => $generated['source'],
            ]);
        }

        // Re-evaluate escalation after an update
        if ($issue->needsEscalation()) {
            $issue->escalate();
        }

        return redirect()
            ->route('issues.show', $issue)
            ->with('success', 'Issue #' . $issue->id . ' updated.');
    }

    // -------------------------------------------------------------------------
    // API endpoints (return JSON)
    // -------------------------------------------------------------------------

    public function apiIndex(Request $request): JsonResponse
    {
        $issues = $this->buildQuery($request)
            ->latest()
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($issues);
    }

    public function apiShow(Issue $issue): JsonResponse
    {
        return response()->json($issue);
    }

    public function apiStore(StoreIssueRequest $request): JsonResponse
    {
        $data = $request->validated();

        $generated = $this->ai->generate(
            $data['title'],
            $data['description'],
            $data['priority'],
            $data['category']
        );

        $issue = Issue::create([
            ...$data,
            'summary'        => $generated['summary'],
            'next_action'    => $generated['next_action'],
            'summary_source' => $generated['source'],
        ]);

        if ($issue->needsEscalation()) {
            $issue->escalate();
        }

        return response()->json($issue, 201);
    }

    public function apiUpdate(UpdateIssueRequest $request, Issue $issue): JsonResponse
    {
        $data = $request->validated();
        $issue->update($data);

        if (array_intersect_key($data, array_flip(['title', 'description', 'priority', 'category']))) {
            $generated = $this->ai->generate(
                $issue->title,
                $issue->description,
                $issue->priority,
                $issue->category
            );
            $issue->update([
                'summary'        => $generated['summary'],
                'next_action'    => $generated['next_action'],
                'summary_source' => $generated['source'],
            ]);
        }

        if ($issue->needsEscalation()) {
            $issue->escalate();
        }

        return response()->json($issue->fresh());
    }

    // -------------------------------------------------------------------------
    // Shared query builder (filters)
    // -------------------------------------------------------------------------

    private function buildQuery(Request $request)
    {
        $query = Issue::query();

        if ($status = $request->query('status')) {
            $query->byStatus($status);
        }

        if ($priority = $request->query('priority')) {
            $query->byPriority($priority);
        }

        if ($category = $request->query('category')) {
            $query->byCategory($category);
        }

        if ($request->boolean('escalated')) {
            $query->escalated();
        }

        return $query;
    }
}
