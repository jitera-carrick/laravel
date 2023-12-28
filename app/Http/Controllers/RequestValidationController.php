<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class RequestValidationController extends Controller
{
    public function validateRequestInput(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'area' => 'required|string|max:255', // Updated validation rule for 'area'
            'menu' => 'required|string|max:255', // Updated validation rule for 'menu'
            'hair_concerns' => 'nullable|string|max:3000',
        ], [
            'area.required' => 'Please select a valid area.', // Updated error message for 'area'
            'menu.required' => 'Please select a valid menu.', // Updated error message for 'menu'
            'hair_concerns.max' => 'Hair concerns and requests must be under 3000 characters.', // Updated error message for 'hair_concerns'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400); // Changed status code to 400 for invalid input
        }

        return response()->json(['message' => 'Input is valid.'], 200);
    }
}
