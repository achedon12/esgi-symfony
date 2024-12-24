<?php

namespace App\Enum;

enum LanguageEnum: String
{
    case FRENCH = 'fr';
    case ENGLISH = 'en';

    public static function getAvailableLanguages(): array
    {
        return [
            'French' => self::FRENCH,
            'English' => self::ENGLISH,
        ];
    }
}