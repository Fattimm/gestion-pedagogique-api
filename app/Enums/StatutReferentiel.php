<?php

namespace App\Enums;

enum StatutReferentiel: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';
    case ARCHIVER = 'archiver';
}
