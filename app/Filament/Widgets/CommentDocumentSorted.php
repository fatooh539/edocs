<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\DocumentComment;
use Filament\Tables\Columns\TextColumn;

class CommentDocumentSorted extends TableWidget
{
       
    public function table(Table $table): Table
    {
        return $table
            ->heading('أحدث التعليقات على المستندات')
            ->query(fn (): 
            Builder => DocumentComment::query()
            ->whereHas('route', function ($query) {
                $query->where('to_department_id', auth()->user()->department_id)
                ->orWhere('from_department_id', auth()->user()->department_id);
            })
            ->orderBy('created_at', 'desc')
            )
            ->emptyStateHeading('لا توجد تعليقات جديدة')
            ->columns([
                TextColumn::make('route.document.subject')
                ->description(fn ($record) => $record->user->name)
                ->label(__('doc.subject')),
                TextColumn::make('route.document.body')
                ->label(__('doc.body'))
                   ->limit(10)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
               
                TextColumn::make('comment')
                ->label(__('doc.comment_label')),

                TextColumn::make('created_at')
                    ->since()
                    ->dateTimeTooltip(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
