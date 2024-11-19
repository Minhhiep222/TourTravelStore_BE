<?php

namespace App\Events;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->message->conversation_id);

    }

     // Thêm này để specify event name
    public function broadcastAs()
    {
        return 'MessageSent';
    }

    public function broadcastWith()
    {
        \Log::info('Broadcasting message', [
            'message' => $this->message->toArray(),
            'conversation_id' => $this->message->conversation_id
        ]);

        return [
            'message' => $this->message->toArray(),
            'sender' => $this->message->sender->toArray()
        ];
    }
}