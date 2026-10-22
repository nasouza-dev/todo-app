<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return Task::orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $request->merge([
            'title' => is_string($request->input('title'))
                ? trim($request->input('title'))
                : $request->input('title'),
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        return Task::create([
            'title' => $validated['title'],
            'completed' => false,
        ]);
    }

    public function update(Request $request, Task $task)
    {
        if ($request->has('title')) {
            $request->merge([
                'title' => is_string($request->input('title'))
                    ? trim($request->input('title'))
                    : $request->input('title'),
            ]);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'completed' => 'sometimes|boolean',
        ]);

        $task->update($validated);

        return $task->fresh();
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
