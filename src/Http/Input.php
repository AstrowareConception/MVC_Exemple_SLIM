<?php

declare(strict_types=1);

namespace App\Http;

use InvalidArgumentException;

/** Convertit les données externes sans laisser PHP deviner leurs types. */
final class Input
{
    /** @param array<string,mixed> $data */
    public static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? trim($value) : '';
    }

    /** @param array<string,mixed> $data */
    public static function nullableInt(array $data, string $key, string $label): ?int
    {
        $value = $data[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value)) {
            return $value;
        }
        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException("{$label} doit être un nombre entier.");
        }

        return (int) $value;
    }

    public static function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : (int) $id;
    }
}
