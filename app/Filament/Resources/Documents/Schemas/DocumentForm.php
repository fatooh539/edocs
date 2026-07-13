<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\Priority;
use App\Enums\Status;
use App\Models\Department;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2) // 🔽 تحديد عمودين للمخطط الرئيسي لتمكين التقسيم الجانبي للأقسام أدناه
            ->components([

                // 1. قسم بيانات النظام المدمج بالأعلى (يظهر الرقم والتاريخ في نفس السطر مع باقي الحقول)
                Section::make()
                    // ->icon('heroicon-o-document-text')
                    ->columns(['md' => 4]) // 🔽 تقسيم الحقول داخلياً إلى 4 أعمدة لتصطف في سطر واحد
                    ->columnSpanFull()
                    // ->inlineLabel()
                    ->schema([


                       
                        TextInput::make('subject')
                            ->hiddenLabel()
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('doc.subject'))
                            ->columnSpanFull(),

                             Select::make('to_departments')
                            ->hiddenLabel()
                            ->multiple()
                            ->options(function() {
                                return Department::where('id', '!=', auth()->user()->department_id)->pluck('name', 'id');
                            })
                           ->placeholder(__('doc.receiver'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->dehydrated(true)
                             ->columnSpan(2),

                        Select::make('to_cc')
                            ->hiddenLabel()
                            ->placeholder(__('doc.cc'))
                            ->options(function() {
                                return Department::where('id', '!=', auth()->user()->department_id)->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->dehydrated(true)
                            ->columnSpan(2),

                             RichEditor::make('body')
                             ->placeholder(__('doc.body_doc'))
                            ->hiddenLabel()
                            ->required()
                            ->maxLength(65535)
                            ->extraAttributes([
                                'style' => 'min-height: 140px;',
                            ])
                            ->columnSpanFull(),

                              SpatieMediaLibraryFileUpload::make('attachment_file')
                            ->hiddenLabel()
                          //  ->collection('attachment_file')
                           // ->disk('public')
                           // ->visibility('public')
                            ->multiple()
                           // ->reorderable()
                           // ->panelLayout('grid')
                            ->columnSpan(2),

                             Select::make('priority')
                            ->label(__('doc.priority'))
                            ->options(Priority::class)
                            ->required()
                            ->native(false),

                        Toggle::make('confidential')
                            ->label(__('doc.confidential'))
                            ->onColor('danger')
                            ->inline(false),



                        Hidden::make('document_no')
                            ->default(fn() => 'DOC-' . now()->format('Ymd') . '-' . random_int(1000, 9999))
                        ,
                        Hidden::make('document_date')->default(now()),
                        Hidden::make('status')->default(Status::SENT),
                         Hidden::make('sender_department_id')
                            ->default(auth()->user()->department_id),
                    ]),

                // 2. قسم التوجيه والموضوع (يأخذ نصف الصفحة الأيمن/الأيسر حسب اتجاه اللغة)
                // Section::make(__('doc.routing_section'))
                //     ->icon('heroicon-o-paper-airplane')
                //     ->columns(1)
                //     ->columnSpan(1) // يأخذ عمود واحد من أصل اثنين
                //     ->schema([
                //         Placeholder::make('sender_department_display')
                //             ->label(__('doc.sender'))
                //             ->content(auth()->user()->department?->name ?? __('doc.undefined')),

                       



                       
                       
                //     ]),

                // 3. قسم النص والمرفقات (يأخذ النصف الثاني المقابل لقسم التوجيه)
                // Section::make(__('doc.content_section'))
                //     ->icon('heroicon-o-folder-open')
                //     ->columns(1)
                //     ->columnSpan(1) // يأخذ عمود واحد من أصل اثنين
                //     ->schema([
                //         RichEditor::make('body')
                //             ->label(__('doc.body'))
                //             ->required()
                //             ->maxLength(65535)
                //             ->columnSpanFull(),

                //         SpatieMediaLibraryFileUpload::make('attachment_file')
                //             ->label(__('doc.attachments'))
                //           //  ->collection('attachment_file')
                //            // ->disk('public')
                //            // ->visibility('public')
                //             ->multiple()
                //            // ->reorderable()
                //            // ->panelLayout('grid')
                //             ->columnSpanFull(),
                //     ]),

            ]);
    }
}