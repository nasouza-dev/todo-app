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
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        return Task::create([
            'title' => $request->title,
            'completed' => false,
        ]);
    }

    public function update(Task $task)
    {
        $task->update([
            'completed' => !$task->completed,
        ]);

        return $task;
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
