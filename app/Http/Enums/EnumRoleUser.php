<?php

namespace App\Http\Enums;

enum EnumRoleUser: string
{
    case SUPER_ADMIN = 'super_admin';
    case COMPANY_ADMIN = 'company_admin';
    case SYNDIC = 'syndic';
    case PORTER = 'porter'; //Todo: temp
    case LANDLORD = 'landlord';
    case RESIDENT = 'resident';
}
