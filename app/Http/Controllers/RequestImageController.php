<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\RequestImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class RequestImageController extends Controller
{
    // ... other methods ...

    // New method to delete an image from a request
    public function deleteImage($requestId, $imageId): JsonResponse
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        // Validate that the request exists and belongs to the authenticated user
        $request = Request::where('id', $requestId)->where('user_id', Auth::id())->first();
        if (!$request) {
            return response()->json(['status' => 403, 'message' => 'Request not found or you do not have permission to edit this request.'], 403);
        }

        // Validate that the image exists and is associated with the given request
        $image = $request->requestImages()->where('id', $imageId)->first();
        if (!$image) {
            return response()->json(['status' => 403, 'message' => 'Image not found or you do not have permission to delete this image.'], 403);
        }

        // Attempt to delete the image
        if ($image->delete()) {
            return response()->json(['status' => 200, 'message' => 'Image has been successfully deleted.']);
        } else {
            return response()->json(['status' => 500, 'message' => 'An unexpected error occurred.'], 500);
        }
    }

    // ... other methods ...
}
