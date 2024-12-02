<?php

namespace App\Http\Controllers;

use App\Events\MessageNotifycation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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

    // public function store(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'message' => ['required', 'string', 'max:1000'],
    //     ]);

    //     // dd($request);

    //     $message = $request->user()->messages()->create($validated);

    //     broadcast(new MessageSent($message))->toOthers();

    //     return response()->json($message->load('user'));
    // }

    public function getConversations()
    {
        $userId = Auth::id();
        return Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo'])
            ->get()
            ->map(function ($conversation) use ($userId) {
                return [
                    'id' => $conversation->id,
                    'other_user' => $conversation->getOtherUser($userId),
                    'last_message' => $conversation->messages()->latest()->first(),
                    'unread_count' => $conversation->messages()
                        ->where('sender_id', '!=', $userId)
                        ->whereNull('read_at')
                        ->count(),
                ];
            })
            ->sortByDesc(function ($conversation) {
                return optional($conversation['last_message'])->created_at ?? Carbon::minValue();
            })
            ->values(); // Để reset key của collection
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

        $conversationDetails = [
            'conversation_id' => $conversation->id,
            'other_user' => $conversation->getOtherUser(Auth::id()),
            'last_message' => $message,
            'unread_count' => $conversation->messages()
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->count(),
        ];

        // $receiverId = Conversation::find($conversationId)
        //     ->participants()
        //     ->where('user_id', '!=', auth()->id())
        //     ->first()
        //     ->user_id;

        // dd($conversationDetails);

        // Thêm try-catch để handle lỗi broadcast
        try {
            broadcast(new MessageSent($message->load('sender'),$conversationDetails ))->toOthers();


            // broadcast(new MessageNotifycation($message))->to('notifications.' . $conversationDetails);
            // dd(broadcast(new MessageNotifycation($message))->to('notifications.' . $conversationDetails));
            // dd(broadcast(new MessageSent($message))->toOthers());
        } catch (\Exception $e) {
            \Log::error('Broadcasting Error', [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            "message" => $message,
            "conversation" => $conversationDetails
        ]);
    }


    public function startConversation(Request $request)
    {

        $request->validate([
            'user_id' => 'required'
        ]);

        $currentUserId = Auth::id();

        $otherUserId = $request->user_id;

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
            // dd( $conversation);
        }

        // return $conversation->load(['userOne', 'userTwo']);

        return response()->json([
            "message" => "Success",
            "conversation_id" => $conversation->id,
            "conversation" => $conversation->load(['userOne', 'userTwo'])
        ], 200);
    }

}