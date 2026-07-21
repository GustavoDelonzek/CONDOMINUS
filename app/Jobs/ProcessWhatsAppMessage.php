<?php

namespace App\Jobs;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\User;
use App\Models\Membership;
use App\Models\Occurrence;
use App\Models\Reservation;
use App\Models\CommonArea;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageData;
    private $sessionTtl = 86400;

    const STEP_MAIN_MENU = 'MAIN_MENU';
    const STEP_OCCURRENCE_TITLE = 'OCCURRENCE_TITLE';
    const STEP_OCCURRENCE_DESC = 'OCCURRENCE_DESC';
    const STEP_OCCURRENCE_PHOTO = 'OCCURRENCE_PHOTO';
    const STEP_RESERVATION_AREA = 'RESERVATION_AREA';
    const STEP_RESERVATION_DATE = 'RESERVATION_DATE';
    const STEP_RESERVATION_TIME = 'RESERVATION_TIME';
    const STEP_PROTOCOL_SEARCH = 'PROTOCOL_SEARCH';

    public function __construct(array $messageData)
    {
        $this->messageData = $messageData;
    }

    public function handle(WhatsAppProviderInterface $whatsapp): void
    {
        $phone = $this->messageData['phone'];
        $text = trim($this->messageData['text']);

        if (in_array(strtolower($text), ['0', 'cancel', 'exit', 'cancelar', 'sair'])) {
            $this->resetSession($phone);
            $this->sendMainMenu($phone, $whatsapp);
            return;
        }

        $session = $this->getSessionData($phone);

        if (!$session) {
            $user = User::where('phone_number', $phone)->first();
            if (!$user) {
                $whatsapp->sendMessage($phone, "Hello! We couldn't find your number in our system. Please contact the administration.");
                return;
            }

            $membership = Membership::where('user_id', $user->id)->where('is_active', true)->first();
            if (!$membership) {
                $whatsapp->sendMessage($phone, "Hello {$user->full_name}, you don't have an active membership linked. Please contact the manager.");
                return;
            }

            $session = $this->initSession($phone, $user, $membership);
            $this->sendMainMenu($phone, $whatsapp);
            return;
        }

        $this->routeByStep($phone, $text, $session, $whatsapp);
    }

    private function routeByStep($phone, $text, $session, $whatsapp)
    {
        switch ($session['step']) {
            case self::STEP_MAIN_MENU:
                $this->handleMainMenu($phone, $text, $session, $whatsapp);
                break;
            case self::STEP_OCCURRENCE_TITLE:
            case self::STEP_OCCURRENCE_DESC:
            case self::STEP_OCCURRENCE_PHOTO:
                $this->handleOccurrenceFlow($phone, $text, $session, $whatsapp);
                break;
            case self::STEP_RESERVATION_AREA:
            case self::STEP_RESERVATION_DATE:
            case self::STEP_RESERVATION_TIME:
                $this->handleReservationFlow($phone, $text, $session, $whatsapp);
                break;
            case self::STEP_PROTOCOL_SEARCH:
                $this->handleProtocolFlow($phone, $text, $session, $whatsapp);
                break;
            default:
                $this->sendMainMenu($phone, $whatsapp);
                break;
        }
    }

    private function handleMainMenu($phone, $text, $session, $whatsapp)
    {
        switch ($text) {
            case '1':
                $this->updateStep($phone, self::STEP_OCCURRENCE_TITLE);
                $whatsapp->sendMessage($phone, "*New Occurrence*\nPlease type a short title for your report:");
                break;
            case '2':
                $areas = CommonArea::where('condominium_id', $session['condominium_id'])->where('is_active', true)->get();
                if ($areas->isEmpty()) {
                    $whatsapp->sendMessage($phone, "No common areas available for reservation in this condominium.");
                    $this->sendMainMenu($phone, $whatsapp);
                    return;
                }

                $menu = "*New Reservation*\nSelect the desired area:\n";
                foreach ($areas as $index => $area) {
                    $menu .= ($index + 1) . " - {$area->name}\n";
                }
                $this->updateStep($phone, self::STEP_RESERVATION_AREA, ['available_areas' => $areas->pluck('id')->toArray()]);
                $whatsapp->sendMessage($phone, $menu);
                break;
            case '3':
                $this->updateStep($phone, self::STEP_PROTOCOL_SEARCH);
                $whatsapp->sendMessage($phone, "*Consult Protocol*\nPlease provide the protocol number:");
                break;
            case '4':
                $whatsapp->sendMessage($phone, "*Human Support*\nI'm pausing the bot and notifying our team. Someone will speak with you shortly!");
                $this->resetSession($phone);
                break;
            default:
                $whatsapp->sendMessage($phone, "Invalid option. Choose from 1 to 4 or type 'exit'.");
                $this->sendMainMenu($phone, $whatsapp);
                break;
        }
    }

    private function handleOccurrenceFlow($phone, $text, $session, $whatsapp)
    {
        $formData = json_decode($session['form_data'] ?? '{}', true);

        if ($session['step'] === self::STEP_OCCURRENCE_TITLE) {
            $formData['title'] = $text;
            $this->updateStep($phone, self::STEP_OCCURRENCE_DESC, $formData);
            $whatsapp->sendMessage($phone, "Now, describe what happened in detail:");
        } elseif ($session['step'] === self::STEP_OCCURRENCE_DESC) {
            $formData['description'] = $text;
            $this->updateStep($phone, self::STEP_OCCURRENCE_PHOTO, $formData);
            $whatsapp->sendMessage($phone, "If you'd like, send a photo (or type 'skip'):");
        } elseif ($session['step'] === self::STEP_OCCURRENCE_PHOTO) {
            $occurrence = Occurrence::create([
                'condominium_id' => $session['condominium_id'],
                'user_id' => $session['user_id'],
                'unit_id' => $session['unit_id'],
                'category' => 'WhatsApp',
                'description' => "[{$formData['title']}] " . $formData['description'],
                'status' => 'pending'
            ]);

            $whatsapp->sendMessage($phone, "Occurrence registered successfully!\n*Protocol:* #{$occurrence->id}\n\nPlease wait for administration feedback.");
            $this->resetSession($phone);
            $this->sendMainMenu($phone, $whatsapp);
        }
    }

    private function handleReservationFlow($phone, $text, $session, $whatsapp)
    {
        $formData = json_decode($session['form_data'] ?? '{}', true);

        if ($session['step'] === self::STEP_RESERVATION_AREA) {
            $index = (int)$text - 1;
            if (isset($formData['available_areas'][$index])) {
                $formData['common_area_id'] = $formData['available_areas'][$index];
                $this->updateStep($phone, self::STEP_RESERVATION_DATE, $formData);
                $whatsapp->sendMessage($phone, "Which day do you want to reserve? (Format: DD/MM/YYYY):");
            } else {
                $whatsapp->sendMessage($phone, "Invalid option. Please select a number from the list.");
            }
        } elseif ($session['step'] === self::STEP_RESERVATION_DATE) {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $text);
                if ($date->isPast()) throw new \Exception();
                
                $formData['date'] = $date->format('Y-m-d');
                $this->updateStep($phone, self::STEP_RESERVATION_TIME, $formData);
                $whatsapp->sendMessage($phone, "What is the start time? (Example: 14:00):");
            } catch (\Exception $e) {
                $whatsapp->sendMessage($phone, "Invalid date or in the past. Use the format DD/MM/YYYY.");
            }
        } elseif ($session['step'] === self::STEP_RESERVATION_TIME) {
            try {
                $startTime = Carbon::parse($formData['date'] . ' ' . $text);
                $endTime = $startTime->copy()->addHours(4);

                $exists = Reservation::where('common_area_id', $formData['common_area_id'])
                    ->where('status', 'confirmed')
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->whereBetween('start_time', [$startTime, $endTime])
                          ->orWhereBetween('end_time', [$startTime, $endTime]);
                    })->exists();

                if ($exists) {
                    $whatsapp->sendMessage($phone, "Sorry, this time is already occupied. Try another date or time.");
                    $this->updateStep($phone, self::STEP_RESERVATION_DATE, $formData);
                } else {
                    $reservation = Reservation::create([
                        'condominium_id' => $session['condominium_id'],
                        'user_id' => $session['user_id'],
                        'unit_id' => $session['unit_id'],
                        'common_area_id' => $formData['common_area_id'],
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'status' => 'confirmed'
                    ]);
                    $whatsapp->sendMessage($phone, "Reservation confirmed!\n*Protocol:* #{$reservation->id}\n*Space:* " . CommonArea::find($formData['common_area_id'])->name);
                    $this->resetSession($phone);
                    $this->sendMainMenu($phone, $whatsapp);
                }
            } catch (\Exception $e) {
                $whatsapp->sendMessage($phone, "Invalid time. Example: 19:00");
            }
        }
    }

    private function handleProtocolFlow($phone, $text, $session, $whatsapp)
    {
        $id = str_replace('#', '', $text);
        
        $occurrence = Occurrence::find($id);
        $reservation = $occurrence ? null : Reservation::find($id);

        if ($occurrence) {
            $msg = "*Occurrence Protocol #{$id}*\nStatus: {$occurrence->status}\nDescription: {$occurrence->description}";
            if ($occurrence->admin_response) $msg .= "\nResponse: {$occurrence->admin_response}";
        } elseif ($reservation) {
            $msg = "*Reservation Protocol #{$id}*\nStatus: {$reservation->status}\nDate: " . $reservation->start_time->format('m/d/Y H:i');
        } else {
            $msg = "Protocol not found.";
        }

        $whatsapp->sendMessage($phone, $msg . "\n\nAnything else? (Yes/No)");
        $this->updateStep($phone, self::STEP_MAIN_MENU);
    }

    private function sendMainMenu($phone, $whatsapp)
    {
        $menu = "*ConDominus Bot*\nHello! How can I help you today?\n\n1 - Report Occurrence\n2 - Make Reservation\n3 - Consult Protocol\n4 - Human Support\n\n_Type 0 to cancel at any time._";
        $whatsapp->sendMessage($phone, $menu);
    }

    private function initSession($phone, $user, $membership)
    {
        $data = [
            'user_id' => $user->id,
            'condominium_id' => $membership->condominium_id,
            'unit_id' => $membership->unit_id,
            'step' => self::STEP_MAIN_MENU,
            'form_data' => json_encode([])
        ];

        foreach ($data as $key => $value) {
            Redis::hset("session:{$phone}", $key, $value);
        }
        Redis::expire("session:{$phone}", $this->sessionTtl);

        return $data;
    }

    private function getSessionData($phone)
    {
        $data = Redis::hgetall("session:{$phone}");
        return !empty($data) ? $data : null;
    }

    private function updateStep($phone, $step, $formData = null)
    {
        Redis::hset("session:{$phone}", 'step', $step);
        if ($formData !== null) {
            Redis::hset("session:{$phone}", 'form_data', json_encode($formData));
        }
    }

    private function resetSession($phone)
    {
        Redis::del("session:{$phone}");
    }
}
