<?php

namespace App\Enums;

enum StatutReferentiel: string
{
    case ACTIF = 'Actif';
    case INACTIF = 'Inactif';
    case ARCHIVER = 'Archiver';
}
