<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: int
{
    case Pending = 0;
    case Paid = 1;
    case Void = 2;
    case Failed = 3;

    public function toString():string
    {
        return match($this) {
            self::Paid => 'Paid',
            self::Void => 'Void',
            self::Failed => 'Failed',
            default => 'Pending'
        };
    }
    public function color(): BadgeColor
    {
        return match ($this) {
            self::Paid => BadgeColor::Green,
            self::Void => BadgeColor::Gray,
            self::Failed => BadgeColor::Red,
            default => BadgeColor::Yellow,
        };
    }
}
