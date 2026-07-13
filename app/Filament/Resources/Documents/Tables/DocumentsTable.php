<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\Priority;
use App\enums\status;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentRecipient;
use App\Models\DocumentRoute;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DocumentsTable
{
    // protected static ?array $to_cc = null;
    
    public static function configure(Table $table): Table
    {
        return $table
           ->columns([
                IconColumn::make('confidential')
                    ->label('')
                    ->icon(fn($state) => $state ? 'heroicon-s-lock-closed' : 'heroicon-o-envelope')
                    ->color(fn($state) => $state ? 'danger' : 'gray')
                    ->tooltip(fn($state) => $state ? __('doc.confidential') : null),

                TextColumn::make('subject')
                    ->label(__('doc.subject'))
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->limit(60)
                    ->description(fn(Document $record) => $record->document_no),

                TextColumn::make('receiveDepartment.name')
                    ->label(__('doc.from'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('currentDepartment.name')
                    ->label(__('doc.at_department'))
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('priority')
                    ->label(__('doc.priority'))
                    ->badge()
                    ->color(fn(Document $record) => $record->priority_color),

                TextColumn::make('status')
                    ->label(__('doc.status'))
                    //go to enums\status.php
                    ->badge(Status::class),

                IconColumn::make('attachments_count')
                    ->label('')
                    ->icon('heroicon-o-paper-clip')
                    ->counts('attachments')
                    ->color('gray')
                    ->tooltip(__('doc.attachments_count')),

                TextColumn::make('document_date')
                    ->label(__('doc.date'))
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('doc.created_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('document_date', 'desc')
            ->filters([
                SelectFilter::make('priority')
                    ->label(__('doc.priority'))
                    ->options(Priority::class),

                SelectFilter::make('status')
                    ->label(__('doc.status'))
                    ->options(Status::class),

                SelectFilter::make('sender_department_id')
                    ->label(__('doc.sender_department'))
                    ->relationship('receiveDepartment', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('confidential')
                    ->label(__('doc.confidential_only')),
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye'),

                    // EditAction::make()
                    //     ->icon('heroicon-o-pencil-square'),

                    // 🔽 Action لإعادة التوجيه مضاف إليه الترجمة
                    Action::make('forward')
                        ->label(__('doc.forward'))
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('warning')
                        ->form([
                            Section::make(__('doc.forward_doc'))
                                ->description(__('doc.forward_description'))
                                ->schema([
                                    Select::make('to_departments')
                                        ->label(__('doc.receiving_departments'))
                                        ->options(
                                            Department::where('id', '!=', Auth::user()->department_id)
                                                ->pluck('name', 'id')
                                        )
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->helperText(__('doc.multiple_departments_helper'))
                                        ->placeholder(__('doc.select_departments_placeholder'))
                                        ->columnSpanFull(),

                                    Textarea::make('comment')
                                        ->label(__('doc.instructions_notes'))
                                        ->placeholder(__('doc.forward_notes_placeholder'))
                                        ->rows(3)
                                        ->columnSpanFull(),

                                    Toggle::make('send_to_all')
                                        ->label(__('doc.send_to_all'))
                                        ->default(false)
                                        ->helperText(__('doc.send_to_all_helper')),
                                ]),
                        ])
                        ->action(function ($record, $data) {
                           
                            $user = Auth::user();
                            $departmentIds = $data['to_departments'];
                            $comment = $data['comment'] ?? null;
                            $sendToAll = $data['send_to_all'] ?? false;

                            // التحقق من وجود أقسام محددة
                            if (empty($departmentIds)) {
                                Notification::make()
                                    ->title(__('doc.error'))
                                    ->body(__('doc.select_at_least_one_dept'))
                                    ->danger()
                                    ->send();
                                return;
                            }

                            if ($sendToAll) {
                                // إرسال للجميع دفعة واحدة
                                $routes = self::forwardToMultipleDepartments($record, $departmentIds, $user, $comment);

                                Notification::make()
                                    ->title(__('doc.forwarded_success_title'))
                                    ->body(__('doc.forwarded_to_count_depts', ['count' => count($routes)]))
                                    ->success()
                                    ->send();
                            } else {
                                // إرسال بشكل متسلسل (route واحد ثم التالي)
                                $firstDepartment = Department::find($departmentIds[0]);
                                $route = self::forwardToDepartment($record, $firstDepartment, $user, $comment);

                                // إضافة الأقسام الأخرى كـ routes مستقبلية (pending)
                                if (count($departmentIds) > 1) {
                                    $remainingDepartments = Department::whereIn('id', array_slice($departmentIds, 1))->get();
                                    foreach ($remainingDepartments as $dept) {
                                        // إنشاء routes للحفاظ على التسلسل
                                        $record->routes()->create([
                                            'from_department_id' => $firstDepartment->id,
                                            'to_department_id' => $dept->id,
                                            'sent_by' => $user->id,
                                            'sent_at' => now(),
                                            'route_status' => Status::DRAFT,
                                        ]);
                                    }
                                }

                                Notification::make()
                                    ->title(__('doc.forwarded_success_title'))
                                    ->body(__('doc.forwarded_to_specific_dept', ['name' => $firstDepartment->name]))
                                    ->success()
                                    ->send();
                            }

                            // تحديث حالة المستند
                            $record->update([
                                'status' => Status::SENT,
                            ]);
                        })
                        ->visible(
                            fn($record) =>
                            $record->current_department_id === Auth::user()->department_id
                        )
                        ->modalWidth('lg')
                        ->modalSubmitActionLabel(__('doc.forward')),

                    Action::make('add_comment')
                        ->label(__('doc.add_comment'))
                        ->icon('heroicon-o-chat-bubble-left')
                        ->color('info')
                        ->form([
                            Section::make(__('doc.add_comment'))
                                ->schema([
                                    Textarea::make('comment')
                                        ->label(__('doc.comment_label'))
                                        ->required()
                                        ->rows(4)
                                        ->placeholder(__('doc.comment_placeholder'))
                                        ->helperText(__('doc.comment_helper')),
                                ]),
                        ])
                        ->action(function ($record, $data) {
                            try {
                                DocumentComment::addCommentToDocument($record, $data['comment']);

                                Notification::make()
                                    ->title(__('doc.comment_added_title'))
                                    ->body(__('doc.comment_added_body'))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title(__('doc.error'))
                                    ->body(__('doc.comment_error_body'))
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->modalWidth('md')
                        ->modalSubmitActionLabel(__('doc.add_comment')),

                    Action::make('receive')
                        ->label(__('doc.receive'))
                        ->icon('heroicon-o-inbox')
                        ->color('info')
                        ->visible(
                            fn($record) =>
                            $record->status === Status::SENT &&
                            $record->current_department_id === Auth::user()->department_id
                        )
                        ->action(function ($record) {
                            self::receiveDocument($record, Auth::user());
                        }),

                    Action::make('complete')
                        ->label(__('doc.complete'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(
                            fn($record) =>
                            $record->status === Status::RECEIVED &&
                            $record->current_department_id === Auth::user()->department_id
                        )
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            self::completeDocument($record, Auth::user());
                        }),

                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                ->icon('heroicon-o-ellipsis-horizontal')
                ->hiddenLabel()
                ->button(),
            ])
            
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) => $query->withCount('attachments')
            );
    }

    public static function forwardToDepartment($document, Department $department, User $user, ?string $comment = null): DocumentRoute
    {
              
       
        // إنشاء route جديد
        $route = $document->routes()->create([
            'from_department_id' => $document->current_department_id,
            'to_department_id' => $department->id,
            'sent_by' => $user->id,
            'sent_at' => now(),
            'route_status' => Status::SENT,
        ]);

        // إضافة تعليق إذا وجد
        if ($comment) {
            $route->comments()->create([
                'user_id' => $user->id,
                'comment' => $comment,
            ]);
        }

        // // تحديث المستند
        $document->update([
            'current_department_id' => $department->id,
        ]);

        return $route;
    }

    /**
     * إعادة توجيه المستند إلى عدة أقسام دفعة واحدة
     */
    public static function forwardToMultipleDepartments($document, array $departmentIds, User $user, ?string $comment = null): array
    {
       
        $routes = [];
        $departments = Department::whereIn('id', $departmentIds)->get();

        foreach ($departments as $department) {
            // إنشاء route لكل قسم
            $route = $document->routes()->create([
                'from_department_id' => $document->current_department_id,
                'to_department_id' => $department->id,
                'sent_by' => $user->id,
                'sent_at' => now(),
                'route_status' => Status::DRAFT,
            ]);

            // إضافة تعليق إذا وجد
            if ($comment) {
                $route->comments()->create([
                    'user_id' => $user->id,
                    'comment' => $comment,
                ]);
            }

            $routes[] = $route;
        }

        // تحديث المستند - سيتم إرساله لأول قسم في القائمة
        if (!empty($departments)) {
            $document->update([
                'current_department_id' => $departments->first()->id,
            ]);
        }

        return $routes;
    }

    /**
     * استلام المستند
     */
    public static function receiveDocument($document, User $user): void
    {
        $document->update([
            'status' => status::RECEIVED,
        ]);

        $route = $document->routes()
            ->where('to_department_id', $user->department_id)
            ->whereNull('received_at')
            ->first();

        if ($route) {
            $route->update([
                'received_by' => $user->id,
                'received_at' => now(),
                'route_status' => status::RECEIVED,
            ]);
        }

        Notification::make()
            ->title(__('doc.received_success_title'))
            ->body(__('doc.received_success_body'))
            ->success()
            ->send();
    }

    /**
     * إكمال المستند
     */
    public static function completeDocument($document, User $user): void
    {
        $document->update([
            'status' => Status::COMPLETED,
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);

        $route = $document->routes()
            ->where('to_department_id', $user->department_id)
            ->whereNull('completed_at')
            ->first();

        if ($route) {
            $route->update([
                'completed_at' => now(),
                'route_status' => Status::COMPLETED,
            ]);
        }

        Notification::make()
            ->title(__('doc.completed_success_title'))
            ->body(__('doc.completed_success_body'))
            ->success()
            ->send();
    }
}