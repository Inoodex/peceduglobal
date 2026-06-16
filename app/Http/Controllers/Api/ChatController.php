<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatGuest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\DynamicPusherService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected $pusherService;

    public function __construct(DynamicPusherService $pusherService)
    {
        $this->pusherService = $pusherService;
    }

    /**
     * Initialize chat for a guest.
     */
    public function init(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $guest = ChatGuest::firstOrCreate(
            ['email' => $request->email],
            ['guest_token' => Str::random(64)]
        );

        // Find or create a conversation for this guest
        $conversation = ChatConversation::where('created_by', $guest->id)
            ->where('created_by_type', ChatGuest::class)
            ->where('status', 'open')
            ->first();

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'created_by' => $guest->id,
                'created_by_type' => ChatGuest::class,
                'status' => 'open',
                'subject' => 'Guest Inquiry'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'guest_token' => $guest->guest_token,
                'conversation_id' => $conversation->id,
                'email' => $guest->email
            ]
        ]);
    }

    /**
     * Send a message.
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:chat_conversations,id',
            'message' => 'required|string',
            'sender_id' => 'required',
            'sender_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $message = ChatMessage::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $request->sender_id,
            'sender_type' => $request->sender_type,
            'message' => $request->message,
            'is_read' => false,
        ]);

        // Trigger real-time event via Dynamic Pusher Service
        $this->pusherService->trigger(
            'chat.' . $request->conversation_id,
            'message.sent',
            [
                'message' => $message,
                'sender_id' => $request->sender_id,
                'sender_type' => $request->sender_type
            ]
        );
        
        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    /**
     * Get chat history.
     */
    public function getHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:chat_conversations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $messages = ChatMessage::where('conversation_id', $request->conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
}
