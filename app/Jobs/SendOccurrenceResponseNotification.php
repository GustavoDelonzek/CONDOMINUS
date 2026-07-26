<?php

namespace App\Jobs;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\MessageLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOccurrenceResponseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $condominiumId,
        private readonly string $userId,
        private readonly string $phone,
        private readonly string $text,
        private readonly ?string $instanceName,
    ) {
    }

    public function handle(WhatsAppProviderInterface $whatsapp): void
    {
        if (!$this->instanceName) {
            Log::info("Occurrence notification skipped: condominium {$this->condominiumId} has no connected WhatsApp instance.");
            return;
        }

        MessageLog::create([
            'condominium_id' => $this->condominiumId,
            'user_id' => $this->userId,
            'phone_number' => $this->phone,
            'direction' => 'outbound',
            'content' => $this->text,
        ]);

        $whatsapp->sendMessage($this->instanceName, $this->phone, $this->text);
    }
}
