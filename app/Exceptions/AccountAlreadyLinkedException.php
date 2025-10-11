<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class AccountAlreadyLinkedException extends Exception
{
    /**
     * Create a new exception instance for when a social account is already linked to another user.
     */
    public function __construct(
        public readonly string $provider,
        string $message = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        $message = $message !== '' && $message !== '0' ? $message : "This {$provider} account is already linked to another user.";
        parent::__construct($message, $code, $previous);
    }
}
