<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\ClassUsers;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    // Mendapatkan daftar semua classes
    public function index()
    {
        $classes = Classes::all();
        return response()->json($classes);
    }

    // Menyimpan class baru
    public function store(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'background_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'teacher_id' => 'required|exists:users,id',
        ]);

        // Memindahkan gambar ke direktori public/images
        $imageName = time().'.'.$request->background_img->extension();
        $request->background_img->move(public_path('images'), $imageName);

        // Membuat record class baru
        $class = new Classes();
        $class->class_name = $request->class_name;
        $class->description = $request->description;
        $class->background_img = 'images/'.$imageName; // Simpan path gambar
        $class->teacher_id = $request->teacher_id;
        $class->save();

        Log::info('Class created with ID: ' . $class->id);

        // Tambahkan data ke tabel class_users
        $classUser = new ClassUsers();
        $classUser->class_id = $class->id;
        $classUser->user_id = $request->teacher_id;
        $classUser->save();

        Log::info('ClassUser created: ', ['class_id' => $class->id, 'user_id' => $request->teacher_id]);

        return response()->json(['message' => 'Class created successfully', 'class' => $class], 201);
    }

    // Mendapatkan detail class berdasarkan ID
    public function show($id)
    {
        $class = Classes::findOrFail($id);
        return response()->json($class);
    }

    // Memperbarui class berdasarkan ID
    public function update(Request $request, $id)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'background_img' => 'nullable|string',
            'teacher_id' => 'required|exists:users,id',
        ]);

        $class = Classes::findOrFail($id);
        $class->class_name = $request->class_name;
        $class->description = $request->description;
        $class->background_img = $request->background_img;
        $class->teacher_id = $request->teacher_id;
        $class->save();

        return response()->json(['message' => 'Class updated successfully', 'class' => $class]);
    }

    // Menghapus class berdasarkan ID
    public function destroy($id)
    {
        $class = Classes::findOrFail($id);
        $class->delete();

        return response()->json(['message' => 'Class deleted successfully']);
    }
}
