<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

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

    public static function loginCancelled(): JsonResponse
    {
        return response()->json(['message' => 'Login process has been canceled.'], 200);
    }

    public static function stylistRequestCreated($hairStylistRequest): JsonResponse
    {
        return response()->json(['message' => 'Stylist request successfully sent.', 'request_id' => $hairStylistRequest->id], 200);
    }
}
