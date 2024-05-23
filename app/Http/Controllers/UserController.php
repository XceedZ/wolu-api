<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Classes;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                Auth::login($user);
            } else {
                $user = User::create([
                    'fullname' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                ]);

                Auth::login($user);
            }

            // Buat token setelah login
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullname' => $user->fullname,
                    'nis' => $user->nis,
                    'role' => $user->role,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Tidak dapat login dengan Google'], 500);
        }
    }

    // Metode login manual dan signup seperti yang sudah ada
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($request->only('username', 'password'))) {
            $user = User::where('username', $request->username)->first();
            $token = $user->createToken('auth_token')->plainTextToken;
    
            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullname' => $user->fullname,
                    'nis' => $user->nis,
                    'role' => $user->role,
                ]
            ], 200);
        }
    
        throw ValidationException::withMessages([
            'username' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function signup(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'fullname' => 'required',
            'password' => 'required',
            'username' => 'nullable|unique:users',
            'nis' => 'nullable|integer',
            'role' => 'required|in:' . User::ROLE_STUDENT . ',' . User::ROLE_TEACHER,
        ], [
            'email.unique' => 'Email sudah terdaftar.',
            'username.unique' => 'Username sudah terdaftar.',
            'nis.integer' => 'NIS harus berupa nilai integer.',
            'role.in' => 'Peran tidak valid.',
        ]);

        $user = new User();
        $user->email = $request->email;
        $user->fullname = $request->fullname;
        $user->password = Hash::make($request->password);
        $user->username = $request->username;
        $user->nis = $request->nis;
        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Pengguna berhasil dibuat'], 201);
    }
    public function shareClass($classId)
{
    $class = Classes::find($classId);

    if (!$class) {
        return response()->json(['error' => 'Kelas tidak ditemukan'], 404);
    }

    if (!$class->share_token) {
        $class->generateShareToken();
    }

    $shareLink = url('/joinclasses/' . $class->share_token); // Link yang akan dibagikan

    return response()->json(['share_link' => $shareLink], 200);
}

public function joinClass($shareToken)
{
    $class = Classes::where('share_token', $shareToken)->first();

    if (!$class) {
        return response()->json(['error' => 'Link tidak valid atau kelas tidak ditemukan'], 404);
    }

    $user = Auth::user();
    if ($class->users()->where('user_id', $user->id)->exists()) {
        return response()->json(['message' => 'Anda sudah tergabung dalam kelas ini'], 200);
    }

    // Tambahkan user ke kelas
    $class->users()->attach($user->id);

    return response()->json(['message' => 'Berhasil bergabung dengan kelas'], 200);
}
}
