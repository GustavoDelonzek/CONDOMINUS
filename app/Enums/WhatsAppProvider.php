<?php

namespace App\Enums;

enum WhatsAppProvider: string
{
    case ZAPI = 'zapi';
    case EVOLUTION = 'evolution';
}
