
<?php

use Illuminate\Http\JsonResponse;

class ApiResponse {

    public function successEmailVerified(): JsonResponse
    {
        return response()->json(['message' => 'Email verified successfully'], 200);
    }

    public function errorInvalidToken(): JsonResponse
    {
        return response()->json(['message' => 'The token is invalid or expired'], 400);
    }
    // ... other methods
}
