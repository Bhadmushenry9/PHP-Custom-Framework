<?php
declare(strict_types=1);

namespace App\Helpers;

class ColorHelper
{
    /**
     * Map color names to their hex values.
     */
    public static function mapColorToHex(string $color): string
    {
        return match (strtolower($color)) {
            'green' => '#38a169',
            'red' => '#e53e3e',
            'yellow' => '#d69e2e',
            'gray' => '#718096',
            default => '#999999',
        };
    }
}
