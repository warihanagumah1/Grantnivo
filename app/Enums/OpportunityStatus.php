<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case ClosingSoon = 'closing_soon';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Open',
            self::ClosingSoon => 'Closing soon',
            self::Closed => 'Closed',
            self::Archived => 'Archived',
        };
    }
}
