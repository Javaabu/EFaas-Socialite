<?php


namespace Javaabu\EfaasSocialite\Enums;


abstract class UserTypes
{
    use Enum;

    const MALDIVIAN = '1';
    const WORK_PERMIT_HOLDER = '2';
    const FOREIGNER = '3';

    protected static $descriptions = [
        self::MALDIVIAN => 'Maldivian',
        self::WORK_PERMIT_HOLDER => 'Work Permit Holder',
        self::FOREIGNER => 'Foreigner',
    ];
}
