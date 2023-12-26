<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StylistRequest;
use App\Models\Shop; // Assuming Shop model exists
use Illuminate\Http\Request;
use App\Http\Requests\StylistRequestSubmissionRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade for user authentication
use Illuminate\Support\Facades\Gate; // Import Gate facade for authorization checks

class StylistRequestController extends Controller
{
    // Add your new method below

    /**
     * Update shop information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateShopInformation(Request $request, $id)
    {
        // Authorize the action
        if (!Gate::allows('update-shop', $id)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        // Retrieve the shop model by ID
        $shop = Shop::find($id);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        // Update the shop's name and address
        $shop->name = $validatedData['name'];
        $shop->address = $validatedData['address'];
        $shop->updated_at = now(); // Update the "updated_at" column with the current timestamp

        // Save the changes to the database
        $shop->save();

        // Return a success response
        return response()->json(['message' => 'Shop information updated successfully.']);
    }

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
