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
use Illuminate\Support\Facades\Log; // Import the Log facade
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    // ... other methods ...

    public function sendMessageAndAdjustTreatmentPlan(Request $request)
    {
        // Validate sender and receiver exist
        $sender = User::find($request->sender_id);
        $receiver = User::find($request->receiver_id);

        if (!$sender || !$receiver) {
            // Log the error details
            Log::error('Message sending failed: Invalid sender or receiver ID.', [
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id
            ]);

            // Return a clear error message
            return response()->json(['error' => 'Message sending failed: Invalid sender or receiver ID.'], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'content' => 'required|max:500',
            'sent_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('content')) {
                // Log the error details
                Log::error('Message sending failed: Content exceeds 500 characters.', [
                    'sender_id' => $request->sender_id,
                    'content_length' => strlen($request->content)
                ]);

                return response()->json(['error' => 'You cannot input more than 500 characters.'], 400);
            }
            if ($errors->has('sent_at')) {
                return response()->json(['error' => 'Wrong datetime format.'], 400);
            }
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Create a new message
        $message = new Message();
        $message->content = $request->content;
        $message->sent_at = Carbon::parse($request->sent_at);
        $message->user_id = $request->sender_id;
        $message->receiver_id = $request->receiver_id;
        $message->save();

        // TODO: Adjust treatment plan details here if necessary

        // Send email to the receiver
        try {
            Mail::to($receiver->email)->send(new TalkRoomNewMessage($message->content, url('/talkroom')));
        } catch (\Exception $e) {
            // Log the exception details
            Log::error('Message sending failed: ' . $e->getMessage(), [
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'exception' => $e
            ]);

            // Return a clear error message
            return response()->json(['error' => 'Message sending failed due to an unexpected error.'], 500);
        }

        // Return response
        return response()->json([
            'message_id' => $message->id,
            'sender_id' => $message->user_id,
            'receiver_id' => $message->receiver_id,
            'content' => $message->content,
            'sent_at' => $message->sent_at->toDateTimeString(),
        ]);
    }

    public function sendUserMessage(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:500',
            'recipient_id' => 'required|exists:stylists,user_id',
        ]);

        $user = Auth::user();
        if (!$user || $user->is_logged_in !== true) {
            return response()->json(['error' => 'User must be logged in to send messages.'], 401);
        }

        $recipient = Stylist::where('user_id', $request->recipient_id)->first();
        if (!$recipient) {
            return response()->json(['error' => 'Recipient must be a valid Hair Stylist.'], 404);
        }

        $message = new Message([
            'content' => $request->content,
            'user_id' => $user->id,
            'receiver_id' => $recipient->user_id,
            'sent_at' => Carbon::now(),
            'read' => false,
        ]);
        $message->save();

        Mail::to($recipient->user->email)->send(new TalkRoomNewMessage($message->content, url('/talkroom')));

        return response()->json([
            'message_id' => $message->id,
            'sent_at' => $message->sent_at,
            'read' => $message->read,
        ]);
    }

    // ... other methods ...
}
