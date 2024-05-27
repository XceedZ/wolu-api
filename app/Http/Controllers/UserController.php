<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Classes;
use Illuminate\Support\Str; // Import namespace Str
use Illuminate\Support\Facades\Log; // Import namespace Log
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;


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
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }
            Auth::login($user);
        } else {
            $user = User::create([
                'fullname' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(24)), 
                'role' => null, 
            ]);

            Auth::login($user);
        }

        $expiresAt = Carbon::now()->addWeek();
    
        $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

        return redirect("http://localhost:5173/google-callback?token={$token}&id={$user->id}&username={$user->username}&email={$user->email}&fullname={$user->fullname}&nis={$user->nis}&role={$user->role}&google_id={$user->google_id}");
    } catch (\Exception $e) {
        Log::error('Google login error: ' . $e->getMessage());
        Log::error('Trace: ' . $e->getTraceAsString());

        return response()->json(['error' => 'Tidak dapat login dengan Google', 'message' => $e->getMessage()], 500);
    }
}


    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($request->only('username', 'password'))) {
            $user = User::where('username', $request->username)->first();
    
            $expiresAt = Carbon::now()->addWeek();
    
            $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;
    
            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'google_id' => $user->google_id,
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
