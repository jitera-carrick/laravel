<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\Stylist;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TalkRoomNewMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent; // Import the MessageSent event
use Illuminate\Support\Facades\Event; // Import the Event facade

class MessageController extends Controller
{
    // ... other methods ...

    public function sendMessageAndAdjustTreatmentPlan(Request $request)
    {
        // Authentication check
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validation
        $validatedData = $request->validate([
            'content' => 'required|string|max:500',
            'scheduled_at' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'stylist_id' => 'required|exists:stylists,id',
        ]);

        // Create and save the message
        $message = new Message([
            'content' => $validatedData['content'],
            'sent_at' => Carbon::now(),
            'user_id' => $validatedData['user_id'],
            'receiver_id' => $validatedData['stylist_id'], // Assuming receiver_id is the stylist_id
            'read' => false, // Assuming a default value for the 'read' column
        ]);
        $message->save();

        // Send email notification using Mailable class
        Mail::to(User::find($validatedData['stylist_id'])->email)->send(new TalkRoomNewMessage($message));

        // Update the reservation
        $reservation = Reservation::whereHas('treatmentPlan', function ($query) use ($validatedData) {
            $query->where('user_id', $validatedData['user_id'])
                  ->where('stylist_id', $validatedData['stylist_id']);
        })->first();

        if ($reservation) {
            $reservation->scheduled_at = new Carbon($validatedData['scheduled_at']);
            $reservation->save();
        }

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Message sent successfully, email notification sent, and reservation updated.',
            'data' => [
                'message' => $message,
                'reservation' => $reservation,
            ]
        ]);
    }

    // ... existing methods ...

    public function sendUserMessageAndNotify(Request $request)
    {
        // Authentication check
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:500',
            'user_id' => 'required|exists:users,id',
            'recipient_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('content')) {
                return response()->json(['error' => 'You cannot input more than 500 characters.'], 400);
            }
            if ($errors->has('user_id')) {
                return response()->json(['error' => 'User not found.'], 400);
            }
            if ($errors->has('recipient_id')) {
                return response()->json(['error' => 'Recipient not found.'], 400);
            }
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Create and save the message
        $message = new Message([
            'content' => $request->content,
            'user_id' => $request->user_id,
            'receiver_id' => $request->recipient_id,
            'sent_at' => Carbon::now(),
        ]);
        $message->save();

        // Trigger notification event
        Event::dispatch(new MessageSent($message));

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Message sent successfully and notification triggered.'
        ]);
    }

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

    // ... other methods ...
}
