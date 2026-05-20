<?php

namespace App\Exceptions\Kapso;

use RuntimeException;

class ClosedSessionException extends RuntimeException
{
    public function __construct(
        string $message = '',
        private readonly array $kapsoResponse = [],
    ) {
        parent::__construct($message);
    }

    public function getKapsoResponse(): array
    {
        return $this->kapsoResponse;
    }
}
