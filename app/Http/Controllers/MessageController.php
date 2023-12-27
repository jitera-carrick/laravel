<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\TalkRoomNewMessage;
use Carbon\Carbon;

class MessageController extends Controller
{
    // ... other methods ...

    public function sendMessageAndAdjustTreatmentPlan(MessageRequest $request)
    {
        // Validate sender and receiver exist
        $sender = User::find($request->sender_id);
        $receiver = User::find($request->receiver_id);

        if (!$sender || !$receiver) {
            return response()->json(['error' => 'Invalid sender or receiver ID.'], 404);
        }

        // Ensure that the content does not exceed 500 characters
        if (strlen($request->content) > 500) {
            return response()->json(['error' => 'Content exceeds 500 characters.'], 400);
        }

        // Create a new message
        $message = new Message();
        $message->content = $request->content;
        $message->sent_at = Carbon::now();
        $message->user_id = $request->sender_id;
        $message->receiver_id = $request->receiver_id; // Add receiver_id to the message
        $message->save();

        // Send email to the receiver
        Mail::to($receiver->email)->send(new TalkRoomNewMessage($message->content, url('/talkroom')));

        // Return response
        return response()->json([
            'message_id' => $message->id,
            'sender_id' => $message->user_id,
            'receiver_id' => $message->receiver_id, // Use the receiver_id from the message
            'content' => $message->content,
            'sent_at' => $message->sent_at,
        ]);
    }

    // ... other methods ...
}
