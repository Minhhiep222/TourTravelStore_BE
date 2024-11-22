<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tour;

class TourCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $tour;
    /**
     * Create a new event instance.
     */
    public function __construct(Tour $tour)
    {
        $this->tour = $tour;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('synchronized');
    }

    // public function broadcastWith() {
    //     return [
    //         'hello' => 'there'
    //     ]
    // }

    public function broadcastAs(): string
    {
        return 'notification';
    }

}
