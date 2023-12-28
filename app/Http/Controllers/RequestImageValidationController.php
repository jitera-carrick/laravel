<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RequestImageValidationController extends Controller
{
    public function validateImages(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'images' => 'sometimes|array|max:3',
            'images.*' => 'file|mimes:png,jpg,jpeg|max:5120', // 5MB = 5120KB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Each image must be in png, jpg, or jpeg format and under 5MB.',
                'errors' => $validator->errors()
            ], 400); // Changed from 422 to 400 to meet the requirement
        }

        return response()->json(['message' => 'Images are valid.'], 200);
    }
}
