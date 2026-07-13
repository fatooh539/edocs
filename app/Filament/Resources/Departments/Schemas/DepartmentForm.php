<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department Details')
                    ->description('Manage core identification details for this department.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->alphaNum(),
                    ])->columns(2),

                Section::make('Structure & Hierarchy')
                    ->description('Define the structural placement and visibility of this department.')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent Department')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name'
                                // // Prevent a department from selecting itself as its own parent
                                // modifyRuleUsing: fn($query, $record) => $query->when($record, fn($q) => $q->where('id', '!=', $record->id))
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true)
                            ->required()
                            ->inline(false),
                    ])->columns(2),
            ]);
    }
}
