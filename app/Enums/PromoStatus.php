<?php

namespace App\Enums;

enum PromoStatus: string
{
    case ACTIF = 'Actif';
    case CLOTURER = 'Cloturer';
    case INACTIF = 'Inactif';
}