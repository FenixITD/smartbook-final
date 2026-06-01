<?php

declare(strict_types=1);

namespace App\Services\User;

class FormatNameService
{
    public static function initials(string|null $name): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }

        $parts = explode(' ', trim($name));
        $initials = [];

        foreach ($parts as $part) {
            if ($part !== '') {
                $initials[] = mb_strtoupper(mb_substr($part, 0, 1)).'.';
            }
        }

        return implode(' ', $initials);
    }
}
