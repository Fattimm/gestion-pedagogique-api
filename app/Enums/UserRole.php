<?php
namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case COACH = 'COACH';
    case MANAGER = 'MANAGER';
    case CM = 'CM';
    case APPRENANT = 'APPRENANT';
}
