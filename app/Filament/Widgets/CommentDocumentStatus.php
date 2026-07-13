<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRoute;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommentDocumentStatus extends ChartWidget
{
    // يمكنك تغيير العنوان ليتناسب مع المحتوى الجديد
    protected ?string $heading = 'حركة المعاملات الصادرة والواردة (السنة الحالية)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $userDeptId = Auth::user()->department_id;
        $currentYear = Carbon::now()->year;

        // تهيئة مصفوفات الأشهر الـ 12 بقيم صفرية مبدئياً
        $outgoingData = array_fill(1, 12, 0);
        $incomingData = array_fill(1, 12, 0);

        // 1. استعلام الخطابات الصادرة (من إدارتي)
        $outgoingCounts = DocumentRoute::select(
                DB::raw('MONTH(sent_at) as month'),
                DB::raw('count(*) as total')
            )
            ->where('from_department_id', $userDeptId)
            ->whereYear('sent_at', $currentYear)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // دمج النتائج في مصفوفة الأشهر
        foreach ($outgoingCounts as $month => $count) {
            $outgoingData[$month] = $count;
        }

        // 2. استعلام الخطابات الواردة (إلى إدارتي)
        $incomingCounts = DocumentRoute::select(
                DB::raw('MONTH(sent_at) as month'),
                DB::raw('count(*) as total')
            )
            ->where('to_department_id', $userDeptId)
            ->whereYear('sent_at', $currentYear)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // دمج النتائج في مصفوفة الأشهر
        foreach ($incomingCounts as $month => $count) {
            $incomingData[$month] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'الصادرة',
                    'data' => array_values($outgoingData),
                    'borderColor' => '#f59e0b', // لون برتقالي لتمييز الصادر
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => 'start',
                ],
                [
                    'label' => 'الواردة',
                    'data' => array_values($incomingData),
                    'borderColor' => '#3b82f6', // لون أزرق لتمييز الوارد
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => 'start',
                ],
            ],
            // تسميات الأشهر باللغة العربية
            'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}