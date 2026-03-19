<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth; // laravel's built-in auth features
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * authenticate a company user and issue a sanctum token
     */
    public function login(Request $request): JsonResponse
    {
        // validate credentials
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // this will start a web session and creates a session cookies
        // in which it can wastes server memory
        /* if (! Auth::attempt($credentials)) {
            // generic error to prevent user enumeration
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        } */

        // stateless authentication
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        // load their company
        $user->load('company');

        // create a sanctum token
        $token = $user->createToken('coi-maxxing-app')->plainTextToken;

        return response()->json([
            "token" => $token,
            "user" => [
                "id" => $user->id,
                "email" => $user->email,
                "name" => $user->name,
                "role" => $user->role,
                "company" =>  $user->company ? [
                    "id" => $user->company->id,
                    "name" => $user->company->name
                ] : null
            ]
        ], 200);
    }

    /**
     * invalidate sanctum token
     */
    public function logout(Request $request): JsonResponse
    {
        // revoke the token
        $request->user()->currentAccessToken()->delete();

        // 204 - no content, token is invalid
        return response()->json(null, 204);
    }

    /**
     * return the authenticated user with their company
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('company');

        return response()->json([
            "user" => [
                "id" => $user->id,
                "email" => $user->email,
                "name" => $user->name,
                "role" => $user->role,
                "company" => $user->company ? [
                    "id" => $user->company->id,
                    "name" => $user->company->name
                ] : null
            ]
        ], 200);
    }
}
