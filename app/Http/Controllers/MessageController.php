<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\Stylist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TalkRoomNewMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    // ... other methods ...

    public function sendMessageAndAdjustTreatmentPlan(Request $request)
    {
        // Existing code for sendMessageAndAdjustTreatmentPlan method
        // ...
    }

    public function sendUserMessage(Request $request)
    {
        // Existing code for sendUserMessage method
        // ...
    }

    // ... other methods ...

    public function logMessageFailure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'recipient_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('content')) {
                return response()->json(['error' => 'Message content is required.'], 400);
            }
            if ($errors->has('user_id')) {
                return response()->json(['error' => 'User not found.'], 400);
            }
            if ($errors->has('recipient_id')) {
                return response()->json(['error' => 'Recipient not found.'], 400);
            }
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $user = Auth::user();
        if ($user->id !== (int)$request->user_id) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        // Log the message sending failure details
        Log::error('Message sending failed.', [
            'user_id' => $request->user_id,
            'recipient_id' => $request->recipient_id,
            'content' => $request->content,
        ]);

        // Handle the failure (e.g., retry mechanism, notification to admin, etc.)

        return response()->json([
            'status' => 200,
            'message' => 'Message sending failure has been logged and handled.',
        ]);
    }
}
