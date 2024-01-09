
<?php

use Illuminate\Http\JsonResponse;

namespace App\Http\Responses;

class ApiResponse
{
    public static function failedLoginResponse(string $message): JsonResponse
    {
        return response()->json(['error' => $message], 401);
    }

    public static function loginFailure(): JsonResponse
    {
        return response()->json(['error' => 'Login failed. Please try again or reset your password.'], 401);
    }
}
