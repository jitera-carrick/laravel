<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRequest;
use App\Http\Resources\RequestResource;
use App\Models\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Update the specified hair stylist request.
     *
     * @param  \App\Http\Requests\UpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the ID is an integer
        if (!is_numeric($id) || intval($id) != $id) {
            return response()->json(['error' => 'Invalid request ID.'], 400);
        }

        $hairRequest = Request::find($id);

        // Check if the request exists
        if (!$hairRequest) {
            return response()->json(['error' => 'Request not found.'], 404);
        }

        // Perform the update operation with validated data
        $hairRequest->update($request->validated());

        // Optionally, handle the file upload if 'images' are provided
        if ($request->hasFile('images')) {
            $images = $request->file('images');

            // Check if the number of images exceeds the limit
            if (count($images) > 3) {
                return response()->json(['error' => 'You can only upload up to 3 images.'], 400);
            }

            foreach ($images as $image) {
                // Check for file type and size
                if (!in_array($image->getClientOriginalExtension(), ['png', 'jpg', 'jpeg']) || $image->getSize() > 5242880) {
                    return response()->json(['error' => 'Invalid image format or size. Please upload png, jpg, or jpeg files under 5MB.'], 400);
                }

                // Assuming RequestImage model exists and is related to Request
                $path = $image->store('request_images', 'public');
                $hairRequest->requestImages()->create(['image_path' => $path]);
            }
        }

        // Return the updated request data
        return new RequestResource($hairRequest);
    }
}
