<?php

namespace App\Enum;

enum Gender: String
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';
}