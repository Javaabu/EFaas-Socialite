<?php


namespace Javaabu\EfaasSocialite\Enums;


abstract class VerificationLevels
{
    use Enum;

    const NOT_VERIFIED = '100';
    const VERIFIED_BY_CALLING = '150';
    const USER_MOBILE_PHONE_REGISTERED = '200';
    const VERIFIED_IN_PERSON_LIMITED = '250';
    const VERIFIED_IN_PERSON = '300';

    protected static $descriptions = [
        self::NOT_VERIFIED => 'Not Verified',
        self::VERIFIED_BY_CALLING => 'Verified by calling',
        self::USER_MOBILE_PHONE_REGISTERED => 'Mobile Phone registered in the name of User',
        self::VERIFIED_IN_PERSON_LIMITED => 'Verified in person (Limited)',
        self::VERIFIED_IN_PERSON => 'Verified in person',
    ];
}
