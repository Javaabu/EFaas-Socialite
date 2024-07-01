<?php

namespace Javaabu\EfaasSocialite\Exceptions;

use RuntimeException;

class JwtTokenInvalidException extends RuntimeException
{
    public function __construct($message = '')
    {
        parent::__construct();

        $this->message = $message;
    }
}
