<?php

declare(strict_types=1);

namespace App\Classes\Exceptions;

class MissingMandatoryParameterException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Missing mandatory parameters.');
    }
}
