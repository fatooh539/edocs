<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RoutesRelationManager extends RelationManager
{
    protected static string $relationship = 'routes';

    protected static ?string $title = 'سجل التوجيه';

    // protected static ?string $icon = 'heroicon-o-arrow-path-rounded-square';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('sent_at', 'desc')
            ->columns([
                TextColumn::make('fromDepartment.name')
                    ->label('من قسم'),

                TextColumn::make('toDepartment.name')
                    ->label('إلى قسم')
                    ->weight(FontWeight::SemiBold)
                    ->icon('heroicon-o-arrow-long-left'),

                TextColumn::make('sender.name')
                    ->label('أرسلها'),

                TextColumn::make('route_status')
                    ->label('الحالة')
                    ->badge(),

                TextColumn::make('sent_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('Y-m-d H:i'),

                TextColumn::make('received_at')
                    ->label('تاريخ الاستلام')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('لم تُستلم بعد'),

                TextColumn::make('duration')
                    ->label('مدة المعالجة (أيام)')
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('receive')
                    ->label('استلام')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('warning')
                    ->visible(fn ($record) => is_null($record->received_at))
                    ->action(fn ($record) => $record->receive(Auth::user())),

                Action::make('completeRoute')
                    ->label('إنهاء')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => is_null($record->completed_at))
                    ->action(fn ($record) => $record->complete(Auth::user())),
            ]);
    }
}