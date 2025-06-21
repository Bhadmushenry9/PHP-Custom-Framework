<?php
declare(strict_types=1);

namespace App\Enums;

enum ButtonColor: string
{
    case Primary = 'bg-blue-600 hover:bg-blue-700 text-white';
    case Success = 'bg-green-600 hover:bg-green-700 text-white';
    case Danger  = 'bg-red-600 hover:bg-red-700 text-white';
    case Warning = 'bg-yellow-500 hover:bg-yellow-600 text-black';
    case Info    = 'bg-teal-600 hover:bg-teal-700 text-white';
    case Secondary = 'bg-gray-600 hover:bg-gray-700 text-white';

    public function label(): string
    {
        return match($this) {
            self::Primary => 'Primary',
            self::Success => 'Success',
            self::Danger => 'Danger',
            self::Warning => 'Warning',
            self::Info => 'Info',
            self::Secondary => 'Secondary',
        };
    }
}
