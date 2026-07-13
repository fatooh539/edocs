<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'المرفقات';

    // protected static ?string $icon = 'heroicon-o-paper-clip';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('file_path')
                ->label('الملف')
                ->disk('local')
                ->directory('document-attachments')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->columns([
                TextColumn::make('original_name')
                    ->label('اسم الملف')
                    ->icon(fn ($record) => $record->icon)
                    ->weight(FontWeight::Medium)
                    ->searchable(),

                TextColumn::make('human_size')
                    ->label('الحجم'),

                TextColumn::make('uploader.name')
                    ->label('رفعه'),

                TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إرفاق ملف')
                    ->mutateFormDataUsing(function (array $data): array {
                        $path = $data['file_path'];

                        $data['original_name'] = basename($path);
                        $data['file_name'] = basename($path);
                        $data['disk'] = 'local';
                        $data['extension'] = pathinfo($path, PATHINFO_EXTENSION);
                        $data['uploaded_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('تنزيل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record): void {
                        $record->incrementDownload();
                    })
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->action(fn ($record) => $record->deleteFile()),
            ]);
    }
}