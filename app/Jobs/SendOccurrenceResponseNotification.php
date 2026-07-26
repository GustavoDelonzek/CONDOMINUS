<?php

namespace App\Jobs;

use App\Contracts\WhatsAppProviderInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOccurrenceResponseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $phone,
        private readonly string $text,
    ) {
    }

    public function handle(WhatsAppProviderInterface $whatsapp): void
    {
        $whatsapp->sendMessage($this->phone, $this->text);
    }
}
