<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\Documents\Pages\ViewDocument;
use App\Filament\Resources\Documents\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\Documents\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Documents\RelationManagers\RoutesRelationManager;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Tables\DocumentsTable;

use App\Models\Document;
use App\Models\Department;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Document Resource is designed similar to an email client:
 * - Main list acts like an Inbox
 * - Detailed view is like opening a message
 * - Ability to "Forward" to another department
 */
class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $recordTitleAttribute = 'subject';

    public $receiveDepart = [];

    public $currentDept = null;

    public function mount(): void
    {
        $this->currentDept = Department::find(auth()->user()->department_id)?->name;
    }
    //sort 2
    protected static ?int $navigationSort = 2;
    // 🔽 استخدام الدوال بدلاً من المتغيرات لدعم الترجمة الديناميكية
    // public static function getNavigationGroup(): ?string
    // {
    //     return __('doc.navigation_group');
    // }

    public static function getNavigationLabel(): string
    {
        return __('doc.navigation_label') ;
    }

    public static function getModelLabel(): string
    {
        return __('doc.model_label'). ' - ' . auth()->user()->department->name;
    }

    public static function getPluralModelLabel(): string
    {
        return __('doc.plural_model_label') . ' - ' . auth()->user()->department->name;
    }

    /*
    |--------------------------------------------------------------------------
    | Form 
    |--------------------------------------------------------------------------
    */
    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */
    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // RoutesRelationManager::class,
            // AttachmentsRelationManager::class,
            // CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            // 'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            // 'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    /**
     * Badge for open documents currently with the user's department
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()
            ->inbox()
            ->open()
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}