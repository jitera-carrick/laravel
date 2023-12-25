<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Models\StylistRequest;
use App\Models\Image;
use Exception;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('email'), 'reset_requested' => false], 422);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User does not exist.', 'reset_requested' => false], 404);
            }

            // Generate a unique token
            $token = Str::random(60);

            // Create a new password reset request
            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(config('auth.passwords.users.expire')),
                'status' => 'pending',
            ]);
            $passwordResetRequest->save();

            // Send the password reset email
            // Assuming a Mailable class named 'PasswordResetMailable' exists
            Mail::to($user->email)->send(new \App\Mail\PasswordResetMailable($token));

            return response()->json(['message' => 'Password reset email sent.', 'reset_requested' => true], 200);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'Failed to send password reset email.', 'reset_requested' => false], 500);
        }
    }

    public function validateResetToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['valid' => false, 'message' => 'Token is required.'], 422);
        }

        try {
            $passwordResetRequest = PasswordResetRequest::where('token', $request->token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$passwordResetRequest) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This password reset token is invalid or has expired.'
                ], 404);
            }

            return response()->json([
                'valid' => true,
                'message' => 'The password reset token is valid.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'An error occurred while validating the token.'
            ], 500);
        }
    }

    // New method to handle stylist request submission
    public function submitStylistRequest(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'area' => 'required|string',
            'gender' => 'required|in:male,female,other',
            'birth_date' => 'required|date',
            'display_name' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all(), 'request_created' => false], 422);
        }

        try {
            // Create a new stylist request
            $stylistRequest = new StylistRequest([
                'area' => $request->area,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'display_name' => $request->display_name,
                'menu' => $request->menu,
                'hair_concerns' => $request->hair_concerns,
                'user_id' => $request->user_id,
                'status' => 'pending',
            ]);
            $stylistRequest->save();

            // Handle image uploads
            $this->handleImageUploads($request, $stylistRequest->id);

            return response()->json([
                'message' => 'Stylist request created successfully.',
                'request_created' => true,
                'request_id' => $stylistRequest->id,
                'stylist_request' => $stylistRequest->load('images') // Eager load associated images
            ], 201);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'Failed to create stylist request.', 'request_created' => false], 500);
        }
    }

    // Method to handle image uploads and create image records
    private function handleImageUploads(Request $request, $stylistRequestId)
    {
        foreach ($request->images as $image) {
            $filePath = $image->store('images', 'public'); // Assuming 'public' disk is configured
            $imageRecord = new Image([
                'file_path' => $filePath,
                'stylist_request_id' => $stylistRequestId,
            ]);
            $imageRecord->save();
        }
    }
}
