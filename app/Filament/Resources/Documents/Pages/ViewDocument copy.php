<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Enums\Status;
use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Department;
use App\Models\DocumentAttachment;
use App\Models\DocumentComment;
use App\Models\DocumentRoute;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Auth;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    /**
     * تصميم "رأس الرسالة" ثم "الجسم"، تمامًا كما تُفتح رسالة بريد إلكتروني
     */
    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextEntry::make('subject')
                        ->label(false)
                        // ->size(TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->columnSpanFull(),

                    Grid::make(4)->schema([
                        TextEntry::make('senderDepartment.name')
                            ->label('من')
                            ->icon('heroicon-o-user-circle'),

                        TextEntry::make('currentDepartment.name')
                            ->label('لدى قسم')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('priority')
                            ->label('الأولوية')
                            ->badge()
                            ->color(fn($record) => $record->priority_color),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('document_no')
                            ->label('رقم المعاملة'),

                        TextEntry::make('document_date')
                            ->label('التاريخ')
                            ->date('Y-m-d'),

                        IconEntry::make('confidential')
                            ->label('سرّي')
                            ->boolean(),
                    ]),
                ]),

            Section::make('نص المعاملة')
                ->schema([
                   
                   
                   
                    TextEntry::make('body')
                        ->label(false)
                        ->html()
                        ->columnSpanFull(),


                    ViewEntry::make('attachments')
                        ->label('المرفقات المصاحبة للمعاملة')
                        ->columnSpanFull()
                        ->view('attachments.document-attachments'),



                ]),



            Section::make('التعليقات')
                ->schema([
                    // عرض التعليقات
                    RepeatableEntry::make('comments')
                        ->label(false)
                        ->schema([
                            ViewEntry::make('comment')
                                ->label(false)
                                ->view('attachments.comments'),
                        ])
                        ->state(fn($record) => DocumentComment::getDocumentComments($record))
                        ->columnSpanFull(),

                    // زر إضافة تعليق
                    Actions::make([
                        Action::make('add_comment')
                            ->label('إضافة تعليق')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->color('primary')
                            ->form([
                                Textarea::make('comment')
                                    ->label('التعليق')
                                    ->required()
                                    ->rows(4)
                                    ->placeholder('اكتب تعليقك هنا...'),
                            ])
                            ->action(function ($record, $data) {
                                DocumentComment::addCommentToDocument($record, $data['comment']);

                                Notification::make()
                                    ->title('تم إضافة التعليق')
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
            EditAction::make(),

            // زر "توجيه" شبيه بزر Forward في البريد الإلكتروني
            Action::make('forward')
                ->label('توجيه')
                ->icon('heroicon-o-arrow-uturn-right')
                ->color('primary')
                ->visible(fn() => !$this->record->is_closed)
                ->schema([
                    Select::make('to_department_id')
                        ->label('توجيه إلى قسم')
                        ->options(Department::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Textarea::make('note')
                        ->label('ملاحظة (اختياري)')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $lastRoute = $this->record->routes()->latest()->first();

                    $route = $lastRoute
                        ? $lastRoute->forward(
                            Department::find($data['to_department_id']),
                            Auth::user()
                        )
                        : DocumentRoute::create([
                            'document_id' => $this->record->id,
                            'from_department_id' => $this->record->sender_department_id,
                            'to_department_id' => $data['to_department_id'],
                            'sent_by' => Auth::id(),
                            'sent_at' => now(),
                            'status' => Status::DRAFT,
                        ]);

                    if (!empty($data['note'])) {
                        $route->comments()->create([
                            'user_id' => Auth::id(),
                            'comment' => $data['note'],
                        ]);
                    }

                    // $this->record->update([
                    //     'current_department_id' => $data['to_department_id'],
                    // ]);
        
                    $this->refreshFormData(['status']);
                })
                ->successNotificationTitle('تم توجيه المعاملة بنجاح'),

            // زر "إنهاء" شبيه بأرشفة رسالة بعد الانتهاء منها
            Action::make('complete')
                ->label('إنهاء المعاملة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => !$this->record->is_closed)
                ->action(fn() => $this->record->markAsCompleted(Auth::user()))
                ->successNotificationTitle('تم إنهاء المعاملة'),
        ];
    }
}