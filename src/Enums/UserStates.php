<?php


namespace Javaabu\EfaasSocialite\Enums;


abstract class UserStates
{
    use Enum;

    const PENDING_VERIFICATION = '2';
    const ACTIVE = '3';

    protected static $descriptions = [
        self::PENDING_VERIFICATION => 'Pending Verification',
        self::ACTIVE => 'Active',
    ];
}
