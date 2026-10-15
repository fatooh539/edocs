<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Priority: string implements HasColor, HasLabel
{
    case LOW = 'LOW';
    case NORMAL = 'NORMAL';
    case HIGH = 'HIGH';
    case URGENT = 'URGENT';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::LOW => __('priority.low'),
            self::NORMAL => __('priority.normal'),
            self::HIGH => __('priority.high'),
            self::URGENT => __('priority.urgent'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::LOW => 'gray',
            self::NORMAL => 'warning',
            self::HIGH => 'success',
            self::URGENT => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::LOW => 'heroicon-m-pencil',
            self::NORMAL => 'heroicon-m-eye',
            self::HIGH => 'heroicon-m-check',
            self::URGENT => 'heroicon-m-x-mark',
        };
    }
}