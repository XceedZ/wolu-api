<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Menangani permintaan login ke aplikasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
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

    /**
     * Menangani permintaan pendaftaran untuk aplikasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
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
}
