<?php

declare(strict_types=1);

namespace App\Classes\Exceptions;

class InvalidDateTimeFormatException extends \Exception
{
    /**
     * @param string $expectedFormat
     */
    public function __construct(string $expectedFormat)
    {
        parent::__construct(\sprintf(
            'Invalid DateTime format. Expected format is \'%s\'',
            $expectedFormat
        ));
    }
}
