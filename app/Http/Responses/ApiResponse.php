
<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

namespace App\Http\Responses;

class ApiResponse
{
    public static function loginSuccess($data): JsonResponse
    {
        return response()->json([
            'message' => 'Login successful.',
            'data' => $data
        ], 200);
    }

    public static function loginFailure(): JsonResponse
    {
        return response()->json(['error' => 'Login failed. Please try again or reset your password.'], 401);
    }

    public static function loginCanceled(): JsonResponse
    {
        return response()->json(['message' => 'Login process has been canceled.'], 200);
    }
}
