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

        // Reload to pick up the appended unread_count + relationships
        $conversation = ChatConversation::with(['guest', 'lastMessage'])->find($request->conversation_id);

        // Trigger real-time event via Dynamic Pusher Service
        // 1) Per-conversation channel (for anyone viewing this conversation)
        $this->pusherService->trigger(
            'chat.' . $request->conversation_id,
            'message.sent',
            [
                'message' => $message,
                'sender_id' => $request->sender_id,
                'sender_type' => $request->sender_type
            ]
        );

        // 2) Global admin channel (for admin sidebar/badge updates anywhere in dashboard)
        $this->pusherService->trigger(
            'admin-chat',
            'message.sent',
            [
                'conversation_id' => $request->conversation_id,
                'message' => $message,
                'unread_count' => $conversation->unread_count,
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

    /**
     * Get public connection settings.
     */
    public function getPublicSettings()
    {
        $keys = ['pusher_driver', 'pusher_key', 'pusher_cluster', 'pusher_host', 'pusher_port', 'pusher_scheme'];
        $settings = \App\Models\ChatSetting::whereIn('key', $keys)->get()->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'data' => [
                'pusher_driver' => $settings->get('pusher_driver') ?? 'pusher',
                'pusher_key' => $settings->get('pusher_key') ?? '',
                'pusher_cluster' => $settings->get('pusher_cluster') ?? '',
                'pusher_host' => $settings->get('pusher_host') ?? '',
                'pusher_port' => $settings->get('pusher_port') ?? '',
                'pusher_scheme' => $settings->get('pusher_scheme') ?? 'https',
            ]
        ]);
    }

    /**
     * List all conversations for admin dashboard.
     */
    public function getConversations(Request $request)
    {
        $conversations = ChatConversation::with(['guest', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Get messages history of a specific conversation for admin.
     */
    public function getAdminHistory($id)
    {
        $messages = ChatMessage::where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark all messages as read since admin is opening this conversation
        ChatMessage::where('conversation_id', $id)
            ->where('sender_type', '!=', \App\Models\User::class)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Send a reply from Admin/Consultant.
     */
    public function sendAdminReply(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Get authenticated user ID (admin/consultant)
        $user = auth()->user();

        $message = ChatMessage::create([
            'conversation_id' => $id,
            'sender_id' => $user->id,
            'sender_type' => \App\Models\User::class,
            'message' => $request->message,
            // Admin-sent message starts UNREAD: the ✓ (sent) → ✓✓ (read) receipt
            // flips only once the guest actually opens/views it.
            'is_read' => false,
            'read_at' => null,
        ]);

        // Trigger real-time event via Dynamic Pusher Service
        // 1) Per-conversation channel (student Next.js + admin open conversation)
        $this->pusherService->trigger(
            'chat.' . $id,
            'message.sent',
            [
                'message' => $message,
                'sender_id' => $user->id,
                'sender_type' => \App\Models\User::class
            ]
        );

        // Update the conversation's updated_at timestamp so it floats to top
        ChatConversation::where('id', $id)->touch();

        // Reload to capture last_message after touch
        $conversation = ChatConversation::with(['guest', 'lastMessage'])->find($id);

        // 2) Global admin channel (sidebar refresh, last message update)
        $this->pusherService->trigger(
            'admin-chat',
            'message.sent',
            [
                'conversation_id' => $id,
                'message' => $message,
                'unread_count' => $conversation->unread_count,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    /**
     * Close/end a conversation.
     */
    public function closeConversation($id)
    {
        $conversation = ChatConversation::findOrFail($id);
        $conversation->update(['status' => 'closed']);

        // Trigger event about closure
        // 1) Per-conversation channel
        $this->pusherService->trigger(
            'chat.' . $id,
            'conversation.closed',
            ['conversation_id' => $id]
        );

        // 2) Global admin channel (so sidebar updates everywhere)
        $this->pusherService->trigger(
            'admin-chat',
            'conversation.closed',
            ['conversation_id' => $id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation closed successfully'
        ]);
    }

    /**
     * Mark all guest messages in a conversation as read (for badge reset).
     * Also broadcasts a read-receipt event so the admin UI can flip ✓ → ✓✓.
     */
    public function markConversationRead($id)
    {
        $now = now();

        ChatMessage::where('conversation_id', $id)
            ->where('sender_type', '!=', \App\Models\User::class)
            ->where(function ($q) {
                $q->where('is_read', false)->orWhereNull('read_at');
            })
            ->update(['is_read' => true, 'read_at' => $now]);

        $conversation = ChatConversation::with(['guest', 'lastMessage'])->find($id);

        // Broadcast read receipt on the per-conversation channel so the
        // admin's open ChatWindow can update its ✓✓ state in real time.
        try {
            $this->pusherService->trigger(
                'chat.' . $id,
                'message.read',
                [
                    'conversation_id' => (int) $id,
                    'read_at' => $now->toIso8601String(),
                    'reader_type' => \App\Models\User::class,
                ]
            );
        } catch (\Throwable $e) {
            // Non-fatal: receipt is cosmetic; the DB is already correct.
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => (int) $id,
                'unread_count' => $conversation ? $conversation->unread_count : 0,
            ]
        ]);
    }

    /**
     * Broadcast an admin "typing" signal to the guest (per-conversation channel).
     * Stateless + fire-and-forget — no persistence. Throttle on the client side.
     */
    public function adminTyping(Request $request, $id)
    {
        $conversation = ChatConversation::find($id);
        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        $user = auth()->user();

        try {
            $this->pusherService->trigger(
                'chat.' . $id,
                'typing',
                [
                    'conversation_id' => (int) $id,
                    'sender_id' => $user?->id,
                    'sender_type' => \App\Models\User::class,
                    'sender_name' => $user?->full_name ?? 'Consultant',
                ]
            );
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to broadcast typing'], 500);
        }

        return response()->json(['success' => true]);
    }
}
