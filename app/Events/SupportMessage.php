<?php namespace App\Events;

use App\Models\SupportChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessage implements ShouldBroadcastNow {

  use Dispatchable, InteractsWithSockets, SerializesModels;

  private SupportChat $chat;
  private array $message;

  public function __construct(SupportChat $chat, array $message) {
    $this->chat = $chat;
    $this->message = $message;
  }

  public function broadcastOn() {
    return [new Channel('Support.' . $this->chat->user), new Channel('Support')];
  }

  public function broadcastWith() {
    return [
      'chat' => $this->chat->toArray(),
      'message' => $this->message
    ];
  }

}
