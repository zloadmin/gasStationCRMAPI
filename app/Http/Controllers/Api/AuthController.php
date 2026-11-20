<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Авторизация пользователя.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Неверные учетные данные'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Выход пользователя.
     */
    public function logout(Request $request): JsonResponse
    {
        if($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }


        return response()->json([
            'message' => 'Вы вышли из системы',
        ]);
    }

    /**
     * Текущий пользователь.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
