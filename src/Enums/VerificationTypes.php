<?php


namespace Javaabu\EfaasSocialite\Enums;


abstract class VerificationTypes
{
    use Enum;

    const BIOMETRIC = 'biometric';
    const IN_PERSON = 'in-person';
    const NOT_AVAILABLE = 'NA';

    protected static $descriptions = [
        self::BIOMETRIC => 'Biometric',
        self::IN_PERSON => 'In Person',
        self::NOT_AVAILABLE => 'Not Available',
    ];
}
