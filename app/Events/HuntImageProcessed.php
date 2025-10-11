<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\Hunt\HuntResource;
use App\Models\Hunt;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class HuntImageProcessed implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Hunt $hunt
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('hunts'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $data = [
            'hunt' => HuntResource::make($this->hunt)->resolve(),
        ];

        Log::info('Broadcasting hunt image processed event', [
            'hunt_id' => $this->hunt->id,
            'channel' => 'hunts',
            'event_name' => 'hunt.image.processed',
            'image_url' => $data['hunt']['image_url'] ?? null,
            'status' => $data['hunt']['image_processing_status'] ?? null,
        ]);

        return $data;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'hunt.image.processed';
    }
}
