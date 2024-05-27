<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassUsers;
use App\Models\Classes;
use App\Models\User; // Pastikan untuk mengimpor model User


class ClassUsersController extends Controller
{
    // Method untuk menambahkan pengguna ke kelas
    public function store(Request $request, $class_id, $user_id)
    {
        // Cek apakah kelas dan pengguna tersedia
        $classUser = ClassUsers::where('class_id', $class_id)
                                ->where('user_id', $user_id)
                                ->first();

        if ($classUser) {
            return response()->json(['message' => 'User is already in the class'], 400);
        }

        // Buat entri baru di tabel classes_users
        $classUser = new ClassUsers();
        $classUser->class_id = $class_id;
        $classUser->user_id = $user_id;
        $classUser->save();

        return response()->json(['message' => 'User added to class successfully'], 201);
    }

    // Method untuk menghapus pengguna dari kelas
    public function destroy(Request $request, $class_id, $user_id)
    {
        // Cari entri kelas_pengguna yang sesuai dan hapus
        $classUser = ClassUsers::where('class_id', $class_id)
                                ->where('user_id', $user_id)
                                ->first();

        if (!$classUser) {
            return response()->json(['message' => 'User is not in the class'], 404);
        }

        $classUser->delete();

        return response()->json(['message' => 'User removed from class successfully'], 200);
    }

    // Method untuk mendapatkan daftar pengguna dalam sebuah kelas
    public function getUsersInClass($class_id)
    {
        // Ambil daftar pengguna yang terkait dengan kelas tertentu
        $userIds = ClassUsers::where('class_id', $class_id)->pluck('user_id');

        // Ambil detail pengguna dari tabel Users
        $users = User::whereIn('id', $userIds)->get(['id', 'fullname']);

        return response()->json(['users' => $users], 200);
    }

    public function index($user_id)
    {
        // Ambil daftar kelas yang terhubung dengan pengguna tertentu
        $classes = ClassUsers::where('user_id', $user_id)->pluck('class_id');
    
        // Ambil detail kelas berdasarkan id
        $classDetails = Classes::whereIn('id', $classes)->get();
    
        // Ambil detail guru untuk setiap kelas
        $teacherIds = $classDetails->pluck('teacher_id')->unique();
        $teachers = User::whereIn('id', $teacherIds)->get(['id', 'fullname']);
        $teacherMap = $teachers->pluck('fullname', 'id');
    
        // Format ulang data kelas sesuai kebutuhan
        $formattedClasses = $classDetails->map(function ($class) use ($teacherMap) {
            return [
                'id' => $class->id,
                'class_name' => $class->class_name,
                'description' => $class->description,
                'background_img' => $class->background_img,
                'teacher_id' => $class->teacher_id,
                'teacher_name' => $teacherMap->get($class->teacher_id),
            ];
        });
    
        // Cek apakah pengguna sudah bergabung dalam kelas yang sama
        $existingClass = ClassUsers::where('user_id', $user_id)
                                    ->whereIn('class_id', $classDetails->pluck('id'))
                                    ->exists();
    
        if ($existingClass) {
            return response()->json(['message' => 'Anda telah bergabung dalam kelas ini', 'classes' => $formattedClasses], 200);
        }
    
        return response()->json(['classes' => $formattedClasses], 200);
    }
    
}
