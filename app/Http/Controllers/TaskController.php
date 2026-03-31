<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * GET /api/tasks
     * List tasks sorted by priority (high→low) then due_date asc.
     * Optional ?status= filter.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'done'])],
        ]);

        $tasks = Task::query()
            ->when($request->filled('status'), fn($q) =>
                $q->where('status', $request->status)
            )
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->orderBy('due_date')
            ->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found.',
                'data'    => [],
            ], 200);
        }

        return response()->json(['data' => $tasks], 200);
    }

    /**
     * POST /api/tasks
     * Create a new task. Status always starts as pending.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                // No duplicate title on the same due_date
                Rule::unique('tasks')->where(
                    fn($q) => $q->where('due_date', $request->due_date)
                ),
            ],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $task = Task::create([
            'title'    => $request->title,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status'   => 'pending',
        ]);

        return response()->json(['data' => $task], 201);
    }

    /**
     * PATCH /api/tasks/{id}/status
     * Advance status forward only: pending - in_progress - done.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
    $task = Task::findOrFail($id);

    // Check if task is already at final status
        if ($task->status === 'done') {
        return response()->json([
            'message' => 'Task is already done. No further transitions allowed.',
        ], 422);
        }

    $next = $task->nextStatus();

    // Explicitly reject if they try to send anything other than the next valid status
        if ($request->status !== $next) {
        return response()->json([
            'message'  => 'Invalid status transition.',
            'current'  => $task->status,
            'allowed'  => $next,
            'provided' => $request->status,
        ], 422);
        }

        $task->update(['status' => $next]);

        return response()->json(['data' => $task], 200);
    }

    /**
     * DELETE /api/tasks/{id}
     * Only tasks with status "done" may be deleted.
     */
    public function destroy(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        if ($task->status !== 'done') {
            return response()->json([
                'message' => 'Forbidden. Only tasks with status "done" can be deleted.',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ], 200);
    }

    /**
     * GET /api/tasks/report?date=YYYY-MM-DD
     * Returns task counts grouped by priority and status for a given date.
     */
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        // Build a zeroed-out structure so every combination always appears
        $summary = [];
        foreach (['high', 'medium', 'low'] as $priority) {
            foreach (['pending', 'in_progress', 'done'] as $status) {
                $summary[$priority][$status] = 0;
            }
        }

        // Single grouped query — fill in real counts
        Task::select('priority', 'status', DB::raw('COUNT(*) as total'))
            ->whereDate('due_date', $request->date)
            ->groupBy('priority', 'status')
            ->get()
            ->each(function ($row) use (&$summary) {
                $summary[$row->priority][$row->status] = (int) $row->total;
            });

        return response()->json([
            'date'    => $request->date,
            'summary' => $summary,
        ], 200);
    }
}