<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StylistRequest;
use Illuminate\Http\Request;
use App\Http\Requests\StylistRequestSubmissionRequest;

class StylistRequestController extends Controller
{
    // Add your new method below

    /**
     * Handle the incoming API request for stylist request submissions.
     *
     * @param  \App\Http\Requests\StylistRequestSubmissionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function submitStylistRequest(StylistRequestSubmissionRequest $request)
    {
        // Validate the input to ensure that the "user_id" and "details" fields are not empty.
        $validatedData = $request->validate([
            'user_id' => 'required',
            'details' => 'required',
        ]);

        // Check if the user exists
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Create a new StylistRequest
        $stylistRequest = new StylistRequest([
            'user_id' => $validatedData['user_id'],
            'details' => $validatedData['details'],
            'status' => 'pending', // Assuming 'pending' is a valid status
        ]);

        // Save the new request to the database
        $stylistRequest->save();

        // Return a response with the request_id and status
        return response()->json([
            'request_id' => $stylistRequest->id,
            'status' => $stylistRequest->status,
        ], 201);
    }

    // ... other methods in the controller ...
}
