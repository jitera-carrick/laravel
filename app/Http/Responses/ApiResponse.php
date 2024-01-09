
<?php

use Illuminate\Http\JsonResponse;

namespace App\Http\Responses;

class ApiResponse
{
    public static function loginFailure(): JsonResponse
    {
        return response()->json(['error' => 'Login failed. Please try again or reset your password.'], 401);
    }
}
