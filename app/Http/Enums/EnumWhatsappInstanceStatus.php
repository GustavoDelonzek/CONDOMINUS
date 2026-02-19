<?php

namespace App\Http\Enums;

enum EnumWhatsappInstanceStatus: string
{
    case CONNECTED = 'connected';
    case DISCONNECTED = 'disconnected';
    case QRCODE = 'qrcode';
}
