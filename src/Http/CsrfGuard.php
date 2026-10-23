<?php

declare(strict_types=1);

namespace App\Http;

final readonly class CsrfGuard
{
    public function __construct(private string $token)
    {
    }

    public function token(): string
    {
        return $this->token;
    }

    public function isValid(mixed $candidate): bool
    {
        return is_string($candidate) && hash_equals($this->token, $candidate);
    }
}
