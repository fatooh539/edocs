<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum Status: string implements HasColor, HasLabel, HasIcon
{
    case DRAFT = 'DRAFT';
    case SENT = 'SENT';
    case RECEIVED = 'RECEIVED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case ARCHIVED = 'ARCHIVED';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => __('status.draft'),
            self::SENT => __('status.sent'),
            self::RECEIVED => __('status.received'),
            self::IN_PROGRESS => __('status.in_progress'),
            self::COMPLETED => __('status.completed'),
            self::ARCHIVED => __('status.archived'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'warning',
            self::RECEIVED => 'success',
            self::IN_PROGRESS => 'info',
            self::COMPLETED => 'primary',
            self::ARCHIVED => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-m-pencil',
            self::SENT => 'heroicon-m-eye',
            self::RECEIVED => 'heroicon-m-check',
            self::IN_PROGRESS => 'heroicon-m-clock',
            self::COMPLETED => 'heroicon-m-check-circle',
            self::ARCHIVED => 'heroicon-m-archive',
        };
    }
}