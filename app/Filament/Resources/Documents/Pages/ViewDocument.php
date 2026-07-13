<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Enums\Status;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\Documents\Tables\DocumentsTable; // 🔽 تم استدعاء كلاس الجدول لاستخدام دواله
use App\Models\Department;
use App\Models\DocumentAttachment;
use App\Models\DocumentComment;
use App\Models\DocumentRoute;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Auth;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    /**
     * Designing "Message Header" then "Body", exactly like opening an email
     */
    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextEntry::make('subject')
                        ->label(__('doc.subject'))
                        // ->size(TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->columnSpanFull(),

                    Grid::make(4)->schema([
                        TextEntry::make('senderDepartment.name')
                            ->label(__('doc.from'))
                            ->icon('heroicon-o-user-circle'),

                        TextEntry::make('currentDepartment.name')
                            ->label(__('doc.at_department'))
                            ->badge()
                            ->color('info'),

                        TextEntry::make('priority')
                            ->label(__('doc.priority'))
                            ->badge()
                            ->color(fn($record) => $record->priority_color),

                        TextEntry::make('status')
                            ->label(__('doc.status'))
                            ->badge(),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('document_no')
                            ->label(__('doc.doc_no')),

                        TextEntry::make('document_date')
                            ->label(__('doc.date'))
                            ->date('Y-m-d'),

                        IconEntry::make('confidential')
                            ->label(__('doc.confidential'))
                            ->boolean(),
                    ]),
                ]),

            Section::make(__('doc.document_body'))
                ->schema([
                    TextEntry::make('body')
                        ->label(__('doc.body'))
                        ->html()
                        ->columnSpanFull(),

                    ViewEntry::make('attachments')
                        ->label(__('doc.attached_attachments'))
                        ->columnSpanFull()
                        ->view('attachments.document-attachments'),
                ]),

            Section::make(__('doc.comments'))
                ->schema([
                    // Show comments
                    RepeatableEntry::make('comments')
                        ->label(false)
                        ->schema([
                            ViewEntry::make('comment')
                                ->label(false)
                                ->view('attachments.comments'),
                        ])
                        ->state(fn($record) => DocumentComment::getDocumentComments($record))
                        ->columnSpanFull(),

                    // Add comment button
                    Actions::make([
                        Action::make('add_comment')
                            ->label(__('doc.add_comment'))
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->color('primary')
                            ->form([
                                Textarea::make('comment')
                                    ->label(__('doc.comment_label'))
                                    ->required()
                                    ->rows(4)
                                    ->placeholder(__('doc.comment_placeholder')),
                            ])
                            //visable only if current department is the same as the user's department and not completed
                            ->visible(fn($record) => $record->current_department_id === auth()->user()->department_id && $record->status !== Status::COMPLETED)
                            ->action(function ($record, $data) {
                                DocumentComment::addCommentToDocument($record, $data['comment']);

                                Notification::make()
                                    ->title(__('doc.comment_added_title'))
                                    ->success()
                                    ->send();
                            }),
                    ]),
                ])
                ->collapsible(),
        ]);
    }

    protected static function siblingPreviewActions(DocumentAttachment $current): array
    {
        return $current->document
            ->attachments()
            ->where('id', '!=', $current->id)
            ->get()
            ->map(
                fn (DocumentAttachment $sibling) => MediaAction::make('preview-' . $sibling->id)
                    ->label($sibling->original_name)
                    ->icon($sibling->icon)
                    ->media($sibling->url)
                    ->controlsList(['nodownload', 'nofullscreen', 'noremoteplayback'])
            )
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            // 🔽 1. زر الاستلام (تمت إضافته ليكتمل التوافق مع الجدول)
            Action::make('receive')
                ->label(__('doc.receive'))
                ->icon('heroicon-o-inbox')
                ->color('info')
                ->visible(
                    fn($record) =>
                    $record->status === Status::DRAFT &&
                    $record->current_department_id === Auth::user()->department_id
                )
                ->action(function ($record) {
                    DocumentsTable::receiveDocument($record, Auth::user());
                    $this->refreshFormData(['status']); // تحديث الصفحة بعد الإجراء
                }),

            // 🔽 2. زر إعادة التوجيه (مطابق للجدول تماماً)
            Action::make('forward')
                ->label(__('doc.forward'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('warning')
                // ->visible(fn ($record) => ...) يمكنك تفعيل شرط الظهور إن أردت
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
                ->visible(fn($record) => $record->current_department_id === auth()->user()->department_id && $record->status !== Status::COMPLETED)
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
                        // إرسال للجميع دفعة واحدة باستخدام دالة الجدول
                        $routes = DocumentsTable::forwardToMultipleDepartments($record, $departmentIds, $user, $comment);

                        Notification::make()
                            ->title(__('doc.forwarded_success_title'))
                            ->body(__('doc.forwarded_to_count_depts', ['count' => count($routes)]))
                            ->success()
                            ->send();
                    } else {
                        // إرسال بشكل متسلسل
                        $firstDepartment = Department::find($departmentIds[0]);
                        $route = DocumentsTable::forwardToDepartment($record, $firstDepartment, $user, $comment);

                        if (count($departmentIds) > 1) {
                            $remainingDepartments = Department::whereIn('id', array_slice($departmentIds, 1))->get();
                            foreach ($remainingDepartments as $dept) {
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

                    $record->update(['status' => Status::SENT]);
                    
                    // 🔽 تحديث البيانات في واجهة العرض مباشرة
                    $this->refreshFormData(['status', 'current_department_id']);
                })
                ->modalWidth('lg')
                ->modalSubmitActionLabel(__('doc.forward')),

            // 🔽 3. زر الإكمال/الإنهاء (مطابق للجدول تماماً)
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
                    DocumentsTable::completeDocument($record, Auth::user());
                    $this->refreshFormData(['status']); // تحديث الصفحة بعد الإجراء
                }),
        ];
    }
}