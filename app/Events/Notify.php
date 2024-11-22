<?php
namespace App\Events;

use App\Models\Tour;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\HashSecret;

class Notify implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tour;

    /**
     * Create a new event instance.
     *
     * @param Tour $tour
     */
    public function __construct(Tour $tour)
    {
        // Gán đối tượng tour cho thuộc tính $tour
        $this->tour = $tour;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tour-channel'), // Channel mà sự kiện sẽ phát sóng
        ];
    }

    /**
     * Dữ liệu sẽ được broadcast khi sự kiện xảy ra.
     */
    public function broadcastWith()
    {
        return [
            // 'tour_id' => HashSecret::encrypt($this->tour->id),
            'tour_name' => $this->tour->name,
            'tour_description' => $this->tour->description,
            'encrypt_id' => HashSecret::encrypt($this->tour->id),
            'images' => $this->tour->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => $image->image_url, 
                    'alt_text' => $image->alt_text,
                ];
            }), 
        ];
    }
}
