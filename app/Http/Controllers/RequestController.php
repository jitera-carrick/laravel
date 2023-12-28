<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRequestRequest; // Import the new form request validation class
use App\Models\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    // ... other methods ...

    // New method to update a request
    public function update($id, UpdateRequestRequest $request): JsonResponse
    {
        // Use route model binding to ensure the $id parameter corresponds to an existing Request object
        $requestModel = Request::where('id', $id)->where('user_id', Auth::id())->first();

        // If the Request does not exist or does not belong to the user, return a 403 Forbidden response
        if (!$requestModel) {
            return response()->json(['message' => 'Request not found or you do not have permission to edit this request.'], 403);
        }

        // The UpdateRequestRequest form request class handles the validation
        $validatedData = $request->validated();

        // Update the Request object with the new values
        $requestModel->fill([
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'] ?? $requestModel->hair_concerns, // Use existing value if not provided
        ]);

        // Save the updated Request object
        $requestModel->save();

        // Return a JSON response with a 200 status code and the updated Request object
        return response()->json(['status' => 200, 'request' => $requestModel]);
    }

    // ... other methods ...
}
