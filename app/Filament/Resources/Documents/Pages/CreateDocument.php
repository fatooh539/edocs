<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Enums\Status;
use App\Filament\Resources\Documents\DocumentResource;
use App\Models\DocumentRoute;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Notifications\Notification; // إضافة مسار الإشعارات
use Exception; // التقاط الأخطاء

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    // protected array $departments = [];

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // حفظ الإدارات المرسل إليها وإزالتها من المصفوفة الأساسية
    //     $this->departments = $data['to_departments'] ?? [];
    //     unset($data['to_departments']);
        
    //     $data['created_by'] = auth()->id();
        
    //     return $data;
    // }


    // protected function afterCreate(): void
    // {
    //     $user = auth()->user();
    //     // 1. التحقق الاستباقي: هل المستخدم لديه إدارة؟
    //     if (! $user->department_id) {
    //         Notification::make()
    //             ->danger()
    //             ->title('فشل في الإرسال')
    //             ->body('لا يمكنك إرسال هذا الخطاب لأنك لست تابعاً لأي إدارة في النظام.')
    //             ->send();

    //         // إيقاف العملية فوراً والتراجع عن إنشاء الخطاب (Rollback)
    //         $this->halt(); 
    //     }

    //     // 2. محاولة إنشاء المسارات بأمان
    //     try {
    //         foreach ($this->departments as $departmentId) {
    //            $saveData = [
    //                 'document_id' => $this->record->id,
    //                 'from_department_id' => $user->department_id,
    //                 'to_department_id' => $departmentId,
    //                 'sent_by' => $user->id,
    //                 'sent_at' => now(),
    //                 // تأكد إذا كانت الحالة يجب أن تكون DRAFT أم SENT
    //                 'route_status' => Status::SENT, 
    //             ];
              
    //         }

    //           DocumentRoute::create($saveData);
    //     } catch (Exception $e) {
    //         // 3. في حال وجود خطأ في قاعدة البيانات أثناء الدوران
    //         Notification::make()
    //             ->danger()
    //             ->title('حدث خطأ غير متوقع')
    //             ->body('لم نتمكن من توجيه الخطاب للإدارات المحددة. يرجى مراجعة الدعم الفني.')
    //             ->send();

    //         $this->halt(); // التراجع عن كل شيء
    //     }
    // }

    // // 4. تخصيص رسالة النجاح (التي تظهر للمستخدم بعد اكتمال الإضافة)
    // protected function getCreatedNotification(): ?Notification
    // {
    //     return Notification::make()
    //         ->success()
    //         ->title('تم الحفظ بنجاح')
    //         ->body('تم إنشاء الخطاب وإرساله إلى الإدارات المعنية بنجاح.');
    // }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}