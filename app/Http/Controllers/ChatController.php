<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

}