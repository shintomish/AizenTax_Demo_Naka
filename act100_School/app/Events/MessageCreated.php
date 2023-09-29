<?php

namespace App\Events;

use App\Models\User;
use App\Models\Customer;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $customer_id;
    public $organization_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    // public function __construct(User $user, Message $message)
    public function __construct(User $user, $customer_id, $organization_id,Message $message)
    {
        $this->user            = $user;
        $this->customer_id     = $customer_id;
        $this->organization_id = $organization_id;
        $this->message         = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('chat');         // Public
        // return new PrivateChannel('chat');  // Privateでは、更新されないのでPublicにした
    }
}
