<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\TaskUser; // Pastikan model ini diimpor

class TaskController extends Controller
{
    // Method index untuk mendapatkan semua tasks
    public function index()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'points' => 'nullable|integer',
        'deadline' => 'nullable|date',
        'class_id' => 'required|exists:classes,id',
        'teacher_id' => 'required|exists:users,id',
        'files_upload.*' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,mp4,mkv|max:10240'
    ]);

    $task = Task::create([
        'title' => $request->title,
        'description' => $request->description,
        'points' => $request->points,
        'deadline' => $request->deadline,
        'class_id' => $request->class_id,
        'teacher_id' => $request->teacher_id,
    ]);

    foreach ($request->file('files_upload') as $file) {
        $filePath = $file->store('files', 'public');
        $fileName = $file->getClientOriginalName(); // Dapatkan nama asli file
        TaskFile::create([
            'task_id' => $task->id,
            'file_path' => $filePath,
            'file_name' => $fileName, // Simpan nama file asli
        ]);
    }

    // Simpan ke tabel relasi tasks_users
    $taskUser = new TaskUser();
    $taskUser->task_id = $task->id;
    $taskUser->user_id = $request->teacher_id;
    $taskUser->save();

    return response()->json(['task' => $task->load('files')], 201);
}
    
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,mp4,mkv|max:10240',
        ]);
    
        if ($request->file()) {
            $filePath = $request->file('file')->store('files', 'public');
            $fileName = $request->file('file')->getClientOriginalName(); // Dapatkan nama asli file
            return response()->json(['file_path' => $filePath, 'file_name' => $fileName], 200);
        }
        return response()->json(['error' => 'File upload failed'], 400);
    }
    

    // Method show untuk mendapatkan detail task berdasarkan ID
    public function show($id)
    {
        $task = Task::with('files')->findOrFail($id);
        
        foreach ($task->files as $file) {
            $file->file_name = $file->file_name; 
        }
        
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

    // Method baru untuk mendapatkan semua tasks berdasarkan class_id
    public function getTasksByClassId($classId)
    {
        $tasks = Task::where('class_id', $classId)->get();
        return response()->json($tasks);
    }
}
