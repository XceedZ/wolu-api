<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json(['message' => 'Authenticated']);
    }

    /**
     * Handle a registration request for the application.
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
            'role' => 'boolean',
        ], [
            'email.unique' => 'The email has already been taken.',
            'username.unique' => 'The username has already been taken.',
            'nis.integer' => 'The NIS must be an integer value.'
        ]);

        $user = new User();
        $user->email = $request->email;
        $user->fullname = $request->fullname;
        $user->password = Hash::make($request->password);
        $user->username = $request->username;
        $user->nis = $request->nis;
        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'User created successfully'], 201);
    }
}
