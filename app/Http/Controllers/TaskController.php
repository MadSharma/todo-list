<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('tasks', 'public');
        }
    
        $task = Task::create([
            'title' => $request->title,
            'image' => $imagePath,
        ]);
    
        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'image' => $imagePath ? asset('storage/' . $imagePath) : null
        ]);
    }
    

    public function update(Request $request, Task $task)
    {
        $task->update(['completed' => $request->completed]);
        return response()->json(['success' => true]);
    }

    public function destroy(Task $task)
    {
        if ($task->image) {
            Storage::disk('public')->delete($task->image);
        }
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted successfully.']);
    }
}
