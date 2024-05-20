<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task; // Tambahkan baris ini

class TaskController extends Controller
{
    // Method index untuk mendapatkan semua tasks
    public function index()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }

    // Method store untuk menyimpan task baru
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points' => 'nullable|integer',
            'deadline' => 'nullable|date',
            'teacher_id' => 'required|exists:users,id',
        ]);

        $task = new Task();
        $task->class_id = $request->class_id;
        $task->title = $request->title;
        $task->description = $request->description;
        $task->points = $request->points;
        $task->deadline = $request->deadline;
        $task->teacher_id = $request->teacher_id;
        $task->save();

        return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);
    }

    // Method show untuk mendapatkan detail task berdasarkan ID
    public function show($id)
    {
        $task = Task::findOrFail($id);
        return response()->json($task);
    }

    // Method update untuk memperbarui task berdasarkan ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points' => 'nullable|integer',
            'deadline' => 'nullable|date',
            'teacher_id' => 'required|exists:users,id',
        ]);

        $task = Task::findOrFail($id);
        $task->class_id = $request->class_id;
        $task->title = $request->title;
        $task->description = $request->description;
        $task->points = $request->points;
        $task->deadline = $request->deadline;
        $task->teacher_id = $request->teacher_id;
        $task->save();

        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

    // Method destroy untuk menghapus task berdasarkan ID
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
