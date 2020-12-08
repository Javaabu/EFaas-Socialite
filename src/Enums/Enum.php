<?php


namespace Javaabu\EfaasSocialite\Enums;


trait Enum
{
    /**
     * Get tje description for the enum
     *
     * @return string
     */
    public static function getDescription($enum)
    {
        return self::$descriptions[$enum] ?? null;
    }
}
