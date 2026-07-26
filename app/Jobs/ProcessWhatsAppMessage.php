<?php

namespace App\Jobs;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\User;
use App\Models\Membership;
use App\Models\MessageLog;
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
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $sessionTtl = 86400;
    private ?string $currentUserId = null;

    const STEP_MAIN_MENU = 'MAIN_MENU';
    const STEP_OCCURRENCE_TITLE = 'OCCURRENCE_TITLE';
    const STEP_OCCURRENCE_DESC = 'OCCURRENCE_DESC';
    const STEP_OCCURRENCE_PHOTO = 'OCCURRENCE_PHOTO';
    const STEP_RESERVATION_AREA = 'RESERVATION_AREA';
    const STEP_RESERVATION_DATE = 'RESERVATION_DATE';
    const STEP_RESERVATION_TIME = 'RESERVATION_TIME';
    const STEP_PROTOCOL_SEARCH = 'PROTOCOL_SEARCH';
    const STEP_ASK_ANYTHING_ELSE = 'ASK_ANYTHING_ELSE';

    public function __construct(
        private readonly array $messageData,
        private readonly string $condominiumId,
        private readonly string $instanceName,
    ) {
    }

    public function handle(WhatsAppProviderInterface $whatsapp): void
    {
        $phone = $this->messageData['phone'];
        $text = trim($this->messageData['text']);

        if (in_array(strtolower($text), ['0', 'cancelar', 'sair'])) {
            $this->resetSession($phone);
            $this->sendMainMenu($phone, $whatsapp);
            return;
        }

        $session = $this->getSessionData($phone);

        if (!$session) {
            $user = User::where('phone_number', $phone)->first();
            if (!$user) {
                $this->reply($phone, "Olá! Não encontramos o seu número em nosso sistema. Entre em contato com a administração.", $whatsapp);
                return;
            }

            $this->currentUserId = $user->id;

            $membership = Membership::where('user_id', $user->id)
                ->where('condominium_id', $this->condominiumId)
                ->where('is_active', true)
                ->first();
            if (!$membership) {
                $this->logInbound($phone, $text);
                $this->reply($phone, "Olá, {$user->full_name}! Você não tem nenhum vínculo ativo neste condomínio. Entre em contato com o síndico.", $whatsapp);
                return;
            }

            $this->logInbound($phone, $text);
            $session = $this->initSession($phone, $user, $membership);
            $this->sendMainMenu($phone, $whatsapp);
            return;
        }

        $this->currentUserId = $session['user_id'];
        $this->logInbound($phone, $text);
        $this->routeByStep($phone, $text, $session, $whatsapp);
    }

    private function reply(string $phone, string $text, WhatsAppProviderInterface $whatsapp): void
    {
        if ($this->currentUserId) {
            MessageLog::create([
                'condominium_id' => $this->condominiumId,
                'user_id' => $this->currentUserId,
                'phone_number' => $phone,
                'direction' => 'outbound',
                'content' => $text,
            ]);
        }

        $whatsapp->sendMessage($this->instanceName, $phone, $text);
    }

    private function logInbound(string $phone, string $text): void
    {
        if (!$this->currentUserId) {
            return;
        }

        MessageLog::create([
            'condominium_id' => $this->condominiumId,
            'user_id' => $this->currentUserId,
            'phone_number' => $phone,
            'direction' => 'inbound',
            'content' => $text,
        ]);
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
            case self::STEP_ASK_ANYTHING_ELSE:
                $this->handleAskAnythingElse($phone, $text, $session, $whatsapp);
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
                if (empty($session['unit_id'])) {
                    $this->reply($phone, "Essa opção é exclusiva para moradores vinculados a uma unidade. Como síndico, use o painel administrativo para gerenciar ocorrências.", $whatsapp);
                    $this->sendMainMenu($phone, $whatsapp);
                    break;
                }
                $this->updateStep($phone, self::STEP_OCCURRENCE_TITLE);
                $this->reply($phone, "*Nova Ocorrência*\nDigite um título curto para o seu registro:", $whatsapp);
                break;
            case '2':
                if (empty($session['unit_id'])) {
                    $this->reply($phone, "Essa opção é exclusiva para moradores vinculados a uma unidade. Como síndico, use o painel administrativo para gerenciar reservas.", $whatsapp);
                    $this->sendMainMenu($phone, $whatsapp);
                    break;
                }
                $areas = CommonArea::where('condominium_id', $session['condominium_id'])->where('is_active', true)->get();
                if ($areas->isEmpty()) {
                    $this->reply($phone, "Não há áreas comuns disponíveis para reserva neste condomínio.", $whatsapp);
                    $this->sendMainMenu($phone, $whatsapp);
                    return;
                }

                $menu = "*Nova Reserva*\nSelecione a área desejada:\n";
                foreach ($areas as $index => $area) {
                    $menu .= ($index + 1) . " - {$area->name}\n";
                }
                $this->updateStep($phone, self::STEP_RESERVATION_AREA, ['available_areas' => $areas->pluck('id')->toArray()]);
                $this->reply($phone, $menu, $whatsapp);
                break;
            case '3':
                $this->updateStep($phone, self::STEP_PROTOCOL_SEARCH);
                $this->reply($phone, "*Consultar Protocolo*\nInforme o número do protocolo:", $whatsapp);
                break;
            case '4':
                $this->reply($phone, "*Atendimento Humano*\nEstou pausando o bot e avisando nossa equipe. Alguém vai falar com você em breve!", $whatsapp);
                $this->resetSession($phone);
                break;
            default:
                $this->reply($phone, "Opção inválida. Escolha de 1 a 4 ou digite 'sair'.", $whatsapp);
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
            $this->reply($phone, "Agora, descreva o que aconteceu em detalhes:", $whatsapp);
        } elseif ($session['step'] === self::STEP_OCCURRENCE_DESC) {
            $formData['description'] = $text;
            $this->updateStep($phone, self::STEP_OCCURRENCE_PHOTO, $formData);
            $this->reply($phone, "Se quiser, envie uma foto (ou digite 'pular'):", $whatsapp);
        } elseif ($session['step'] === self::STEP_OCCURRENCE_PHOTO) {
            $occurrence = Occurrence::create([
                'condominium_id' => $session['condominium_id'],
                'user_id' => $session['user_id'],
                'unit_id' => $session['unit_id'],
                'category' => 'WhatsApp',
                'description' => "[{$formData['title']}] " . $formData['description'],
                'status' => 'open'
            ]);

            $this->reply($phone, "Ocorrência registrada com sucesso!\n*Protocolo:* #{$occurrence->id}\n\nAguarde o retorno da administração.", $whatsapp);
            $this->askAnythingElse($phone, $whatsapp);
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
                $this->reply($phone, "Qual dia você quer reservar? (Formato: DD/MM/AAAA):", $whatsapp);
            } else {
                $this->reply($phone, "Opção inválida. Selecione um número da lista.", $whatsapp);
            }
        } elseif ($session['step'] === self::STEP_RESERVATION_DATE) {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $text);
                if ($date->isPast()) throw new \Exception();

                $formData['date'] = $date->format('Y-m-d');
                $this->updateStep($phone, self::STEP_RESERVATION_TIME, $formData);
                $this->reply($phone, "Qual o horário de início? (Exemplo: 14:00):", $whatsapp);
            } catch (\Exception $e) {
                $this->reply($phone, "Data inválida ou no passado. Use o formato DD/MM/AAAA.", $whatsapp);
            }
        } elseif ($session['step'] === self::STEP_RESERVATION_TIME) {
            try {
                $startTime = Carbon::parse($formData['date'] . ' ' . $text);
                $commonArea = CommonArea::find($formData['common_area_id']);
                $rules = $commonArea->booking_rules ?? [];

                $maxDurationHours = data_get($rules, 'max_duration_hours') ?: 4;
                $endTime = $startTime->copy()->addHours($maxDurationHours);

                $minAdvanceHours = data_get($rules, 'min_advance_hours');
                $hoursUntilStart = ($startTime->timestamp - now()->timestamp) / 3600;
                if ($minAdvanceHours && $hoursUntilStart < $minAdvanceHours) {
                    $this->reply($phone, "Essa reserva precisa ser feita com pelo menos {$minAdvanceHours}h de antecedência. Escolha outra data ou horário.", $whatsapp);
                    $this->updateStep($phone, self::STEP_RESERVATION_DATE, $formData);
                    return;
                }

                $opensAt = data_get($rules, 'opens_at');
                $closesAt = data_get($rules, 'closes_at');
                if ($opensAt && $closesAt) {
                    $dayOpen = Carbon::parse($formData['date'] . ' ' . $opensAt);
                    $dayClose = Carbon::parse($formData['date'] . ' ' . $closesAt);
                    if ($dayClose->gt($dayOpen) && ($startTime->lt($dayOpen) || $endTime->gt($dayClose))) {
                        $this->reply($phone, "Esse espaço funciona das {$opensAt} às {$closesAt}. Escolha um horário dentro desse intervalo.", $whatsapp);
                        $this->updateStep($phone, self::STEP_RESERVATION_DATE, $formData);
                        return;
                    }
                }

                $maxPerMonth = data_get($rules, 'max_reservations_per_unit_per_month');
                if ($maxPerMonth) {
                    $countThisMonth = Reservation::where('unit_id', $session['unit_id'])
                        ->where('common_area_id', $formData['common_area_id'])
                        ->whereIn('status', ['pending', 'confirmed', 'completed'])
                        ->whereBetween('start_time', [$startTime->copy()->startOfMonth(), $startTime->copy()->endOfMonth()])
                        ->count();
                    if ($countThisMonth >= $maxPerMonth) {
                        $this->reply($phone, "Você já atingiu o limite de {$maxPerMonth} reserva(s) por mês para esta unidade neste espaço.", $whatsapp);
                        $this->askAnythingElse($phone, $whatsapp);
                        return;
                    }
                }

                $exists = Reservation::where('common_area_id', $formData['common_area_id'])
                    ->where('status', 'confirmed')
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->whereBetween('start_time', [$startTime, $endTime])
                          ->orWhereBetween('end_time', [$startTime, $endTime]);
                    })->exists();

                if ($exists) {
                    $this->reply($phone, "Desculpe, esse horário já está ocupado. Tente outra data ou horário.", $whatsapp);
                    $this->updateStep($phone, self::STEP_RESERVATION_DATE, $formData);
                } else {
                    $requiresApproval = data_get($rules, 'requires_approval', false);

                    $reservation = Reservation::create([
                        'condominium_id' => $session['condominium_id'],
                        'user_id' => $session['user_id'],
                        'unit_id' => $session['unit_id'],
                        'common_area_id' => $formData['common_area_id'],
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'status' => $requiresApproval ? 'pending' : 'confirmed'
                    ]);

                    $statusMessage = $requiresApproval
                        ? "Reserva solicitada! Aguardando aprovação do síndico.\n*Protocolo:* #{$reservation->id}\n*Espaço:* {$commonArea->name}"
                        : "Reserva confirmada!\n*Protocolo:* #{$reservation->id}\n*Espaço:* {$commonArea->name}";
                    $this->reply($phone, $statusMessage, $whatsapp);
                    $this->askAnythingElse($phone, $whatsapp);
                }
            } catch (\Exception $e) {
                $this->reply($phone, "Horário inválido. Exemplo: 19:00", $whatsapp);
            }
        }
    }

    private function handleProtocolFlow($phone, $text, $session, $whatsapp)
    {
        $id = str_replace('#', '', trim($text));
        $occurrence = null;
        $reservation = null;

        if (Str::isUuid($id)) {
            $occurrence = Occurrence::find($id);
            $reservation = $occurrence ? null : Reservation::find($id);
        }

        if ($occurrence) {
            $msg = "*Protocolo de Ocorrência #{$id}*\nStatus: {$occurrence->status}\nDescrição: {$occurrence->description}";
            if ($occurrence->admin_response) $msg .= "\nResposta: {$occurrence->admin_response}";
        } elseif ($reservation) {
            $msg = "*Protocolo de Reserva #{$id}*\nStatus: {$reservation->status}\nData: " . $reservation->start_time->format('d/m/Y H:i');
        } else {
            $msg = "Protocolo não encontrado.";
        }

        $this->reply($phone, $msg, $whatsapp);
        $this->askAnythingElse($phone, $whatsapp);
    }

    private function askAnythingElse($phone, $whatsapp)
    {
        $this->updateStep($phone, self::STEP_ASK_ANYTHING_ELSE);
        $this->reply($phone, "Posso te ajudar com mais alguma coisa? (Sim/Não)", $whatsapp);
    }

    private function handleAskAnythingElse($phone, $text, $session, $whatsapp)
    {
        $normalized = strtolower(trim($text));

        if (in_array($normalized, ['sim', 's', 'yes'])) {
            $this->updateStep($phone, self::STEP_MAIN_MENU);
            $this->sendMainMenu($phone, $whatsapp);
            return;
        }

        if (in_array($normalized, ['não', 'nao', 'n', 'no'])) {
            $this->reply($phone, "Tudo certo! Fico à disposição. Até mais 👋", $whatsapp);
            $this->resetSession($phone);
            return;
        }

        $this->reply($phone, "Não entendi. Responda com 'sim' ou 'não'.", $whatsapp);
    }

    private function sendMainMenu($phone, $whatsapp)
    {
        $menu = "*Bot Condominus*\nOlá! Como posso te ajudar hoje?\n\n1 - Registrar Ocorrência\n2 - Fazer Reserva\n3 - Consultar Protocolo\n4 - Falar com Atendente\n\n_Digite 0 para cancelar a qualquer momento._";
        $this->reply($phone, $menu, $whatsapp);
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
