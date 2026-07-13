<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * ملاحظة: يعتمد هذا المدير على علاقة "comments" الجديدة التي تمت
 * إضافتها إلى موديل Document (hasManyThrough عبر DocumentRoute)
 * حتى تظهر كل التعليقات على مختلف مسارات التوجيه في مكان واحد،
 * تمامًا كسلسلة الردود (thread) أسفل رسالة بريد إلكتروني.
 */
class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'المناقشة';

    // protected static ?string $icon = 'heroicon-o-chat-bubble-left-right';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('document_route_id')
                ->label('على مسار التوجيه')
                ->relationship('route', 'id')
                ->getOptionLabelFromRecordUsing(
                    fn ($record) => $record->toDepartment?->name ?? '—'
                )
                ->default(fn () => $this->getOwnerRecord()->routes()->latest()->value('id'))
                ->required(),

            Textarea::make('comment')
                ->label('التعليق')
                ->required()
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('author_name')
                    ->label('بواسطة')
                    ->weight(FontWeight::SemiBold),

                TextColumn::make('route.toDepartment.name')
                    ->label('قسم المسار')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('comment')
                    ->label('التعليق')
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('الوقت')
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة رد')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === Auth::id()),
            ]);
    }
}