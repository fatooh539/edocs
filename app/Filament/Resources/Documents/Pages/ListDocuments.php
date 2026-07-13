<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Enums\Status;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use App\Models\Department;
use App\Models\DocumentRoute;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    public array $tempRoutingData = [];

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('doc.new_document'))
                ->icon('heroicon-o-pencil-square')
                ->modalWidth('5xl')
                ->modalHeading(
                    fn() => __('doc.doc_date') . ' - ' . now()->format('Y-m-d H:i A') . '  ' .
                    __('doc.doc_no') . '  ' .
                    'DOC-' . now()->format('Ymd') . '-' . 
                    (\App\Models\Document::latest()->first()?->id ?? 0) + 1
                )
                
                ->mutateFormDataUsing(function (array $data): array {
                    $this->tempRoutingData = [
                        'to_departments' => $data['to_departments'] ?? [],
                        'to_cc'          => $data['to_cc'] ?? [],
                        'comment'        => $data['comment'] ?? null,
                        'send_to_all'    => $data['send_to_all'] ?? false,
                    ];
                    
                    unset($data['to_departments'], $data['to_cc'], $data['comment'], $data['send_to_all']);
                    
                    $data['created_by'] = auth()->id();
                    $data['current_department_id'] = auth()->user()->department_id; 
                    $data['status'] = Status::DRAFT; 
                 
                    return $data;
                })
                
                ->after(function (CreateAction $action, Model $record) {
                    $user = auth()->user();

                    if (!$user->department_id) {
                        Notification::make()
                            ->danger()
                            ->title(__('doc.error'))
                            ->body('لا يمكنك إرسال هذا الخطاب لأنك لست تابعاً لأي إدارة في النظام.')
                            ->send();

                        $action->halt();
                    }

                    $departmentIds = $this->tempRoutingData['to_departments'];
                    $ccIds         = $this->tempRoutingData['to_cc'];
                    $comment       = $this->tempRoutingData['comment'];
                    $sendToAll     = $this->tempRoutingData['send_to_all'];

                    if (empty($departmentIds) && empty($ccIds)) {
                        return; 
                    }

                    try {
                        // 1. معالجة الإدارات الأساسية
                        if (!empty($departmentIds)) {
                            if ($sendToAll) {
                                DocumentsTable::forwardToMultipleDepartments($record, $departmentIds, $user, $comment);
                            } else {
                                $firstDepartment = Department::find($departmentIds[0]);
                                DocumentsTable::forwardToDepartment($record, $firstDepartment, $user, $comment);

                                if (count($departmentIds) > 1) {
                                    $remainingDepartments = Department::whereIn('id', array_slice($departmentIds, 1))->get();
                                    foreach ($remainingDepartments as $dept) {
                                        $record->routes()->create([
                                            'from_department_id' => $firstDepartment->id,
                                            'to_department_id'   => $dept->id,
                                            'sent_by'            => $user->id,
                                            'sent_at'            => now(),
                                            'route_status'       => Status::DRAFT,
                                            'is_cc'              => false, // تأكيد أن هذا المسار أساسي وليس نسخة
                                        ]);
                                    }
                                }
                            }
                            
                            $record->update(['status' => Status::SENT]);
                        }

                        // 2. معالجة إدارات النسخة الكربونية (CC)
                        if (!empty($ccIds)) {
                            foreach ($ccIds as $ccId) {
                                if (!in_array($ccId, $departmentIds)) {
                                    $record->routes()->create([
                                        'from_department_id' => $user->department_id,
                                        'to_department_id'   => $ccId,
                                        'sent_by'            => $user->id,
                                        'sent_at'            => now(),
                                        'route_status'       => Status::SENT, 
                                        'is_cc'              => true, // 🟢 تم إضافة الحقل هنا
                                    ]);
                                }
                            }
                        }

                        Notification::make()
                            ->title(__('doc.forwarded_success_title'))
                            ->success()
                            ->send();

                    } catch (Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title(__('doc.error'))
                            ->body('لم نتمكن من توجيه الخطاب للإدارات المحددة. يرجى المحاولة لاحقاً.')
                            ->send();

                        $action->halt();
                    }
                })
        ];
    }

    public function getTabs(): array
    {
        return [
            'inbox' => Tab::make(__('doc.inbox'))
                ->icon('heroicon-o-inbox-arrow-down')
                ->modifyQueryUsing(fn(Builder $query) => $query->inbox())
                ->badge(fn() => $this->getModel()::query()->inbox()->open()->count()),

            'outbox' => Tab::make(__('doc.outbox'))
                ->icon('heroicon-o-paper-airplane')
                ->modifyQueryUsing(fn(Builder $query) => $query->outbox()),

            'open' => Tab::make(__('doc.in_progress'))
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn(Builder $query) => $query->open()),

            'archived' => Tab::make(__('doc.archived_tab'))
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn(Builder $query) => $query->archived()),
            'cc' => Tab::make(__('doc.cc'))
                ->icon('heroicon-o-paper-clip')
                ->modifyQueryUsing(fn(Builder $query) => $query->cc()),
            'all' => Tab::make(__('doc.all_tab'))
                // ->badge(fn() => $this->getModel()::query()->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->myDocuments()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'inbox';
    }
}