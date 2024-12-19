<?php

namespace App\DBAL\Types;

use App\Enum\Gender;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class GenderType extends Type
{
    const NAME = 'gender';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('male', 'female', 'other')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return !empty($value) ? Gender::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof Gender ? $value->value : null;
    }

    public function getName()
    {
        return self::NAME;
    }
}