<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
class ChatController extends Controller
{
    //
    public function index(): JsonResponse
    {
        $messages = Message::with('user')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        // dd($request);

        $message = $request->user()->messages()->create($validated);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->load('user'));
    }

    public function getConversations()
    {
        $userId = Auth::id();
        // dd($userId);
        return Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo'])
            ->get()
            ->map(function ($conversation) use ($userId) {
                // dd($conversation);
                return [
                    'id' => $conversation->id,
                    'other_user' => $conversation->getOtherUser($userId),
                    'last_message' => $conversation->messages()->latest()->first(),
                    'unread_count' => $conversation->messages()
                        ->where('sender_id', '!=', $userId)
                        ->whereNull('read_at')
                        ->count(),
                ];
            }
        );
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Mark messages as read
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();
    }


    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        // Kiểm tra conversation tồn tại và user có quyền
        $conversation = Conversation::findOrFail($conversationId);
        if ($conversation->user_one_id !== Auth::id() &&
            $conversation->user_two_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => Auth::id(),
            'message' => $request->message
        ]);

        // Load sender trước khi broadcast
        $message->load('sender');

        // Thêm try-catch để handle lỗi broadcast
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            \Log::error('Broadcasting Error', [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
        }

        return $message;
    }


    public function startConversation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUserId = Auth::id();
        $otherUserId = $request->user_id;

        // Check if conversation already exists
        $conversation = Conversation::where(function ($query) use ($currentUserId, $otherUserId) {
            $query->where('user_one_id', $currentUserId)
                  ->where('user_two_id', $otherUserId);
        })->orWhere(function ($query) use ($currentUserId, $otherUserId) {
            $query->where('user_one_id', $otherUserId)
                  ->where('user_two_id', $currentUserId);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $currentUserId,
                'user_two_id' => $otherUserId
            ]);
        }

        return $conversation->load(['userOne', 'userTwo']);
    }

}