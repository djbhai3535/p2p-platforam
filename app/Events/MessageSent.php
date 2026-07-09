<?php

namespace App\Events;

use App\Models\OrderMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderMessage $message;

    /**
     * Create a new event instance.
     */
    public function __construct(OrderMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.'.$this->message->order_id),
        ];
    }

    /**
     * Custom attributes to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'order_id' => $this->message->order_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'message' => $this->message->message,
            'attachment_url' => $this->message->attachment_path ? asset('storage/'.$this->message->attachment_path) : null,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
