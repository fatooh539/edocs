<?php

return [
    // العناوين والأقسام الأساسية
    'docs' => 'الوثائق',
    'basic_info_section' => 'بيانات المراسلة الأساسية',
    'routing_section' => 'التوجيه والموضوع',
    'content_section' => 'المحتوى والمرفقات',

    // الحقول والبيانات العامة
    'doc_no' => 'رقم الرسالة',
    'doc_date' => 'تاريخ الرسالة',
    'priority' => 'الأولوية',
    'confidential' => 'رسالة سرّية',
    'sender' => 'من (الجهة المرسلة)',
    'receiver' => 'إلى (الجهة المستقبلة)',
    'cc' => 'نسخة ',
    'subject' => 'الموضوع',
    'body' => 'النص / البيان',
    'body_doc' => 'نص المستند',
    'attachments' => 'المرفقات',
    'undefined' => 'غير محدد',
    'user' => 'المستخدم',

    // نصوص إضافية خاصة بجدول المراسلات (Table Columns & Tooltips)
    'from' => 'من',
    'at_department' => 'لدى قسم',
    'status' => 'الحالة',
    'attachments_count' => 'عدد المرفقات',
    'date' => 'التاريخ',
    'created_at' => 'أُنشئت',
    'sender_department' => 'القسم المرسل',
    'confidential_only' => 'سرّي فقط',
    'actions' => 'إجراءات',

    // الإجراءات والنوافذ المنبثقة (Actions & Modals)
    'forward' => 'إعادة توجيه',
    'forward_doc' => 'إعادة توجيه المستند',
    'forward_description' => 'اختر الأقسام التي تريد إرسال المستند إليها',
    'receiving_departments' => 'الأقسام المستقبلة',
    'select_departments_placeholder' => 'اختر الأقسام...',
    'multiple_departments_helper' => 'يمكنك اختيار أكثر من قسم',
    'instructions_notes' => 'تعليمات / ملاحظات',
    'forward_notes_placeholder' => 'أضف أي تعليمات أو ملاحظات للتوجيه...',
    'send_to_all' => 'إرسال للجميع (بدون ترتيب)',
    'send_to_all_helper' => 'إذا تم التفعيل، سيتم إرسال المستند لجميع الأقسام المحددة دفعة واحدة',
    
    'add_comment' => 'إضافة تعليق',
    'comment_label' => 'التعليق',
    'comment_placeholder' => 'اكتب تعليقك هنا...',
    'comment_helper' => 'يمكنك إضافة أي ملاحظات أو تعليمات',

    'receive' => 'استلام',
    'complete' => 'إكمال',

    // الإشعارات والرسائل (Notifications & Errors)
    'error' => 'خطأ',
    'select_at_least_one_dept' => 'يرجى اختيار قسم واحد على الأقل',
    'forwarded_success_title' => 'تم إعادة التوجيه بنجاح',
    'forwarded_to_count_depts' => 'تم إرسال المستند إلى :count أقسام',
    'forwarded_to_specific_dept' => 'تم إرسال المستند إلى القسم: :name',
    
    'comment_added_title' => 'تم إضافة التعليق',
    'comment_added_body' => 'تم إضافة تعليقك بنجاح على المستند',
    'comment_error_body' => 'حدث خطأ أثناء إضافة التعليق',

    'received_success_title' => 'تم استلام المستند',
    'received_success_body' => 'تم استلام المستند بنجاح.',
    
    'completed_success_title' => 'تم إكمال المستند',
    'completed_success_body' => 'تم إكمال المستند بنجاح.',

    // Tabs & Header Actions
    'new_document' => 'رسالة جديدة',
    'inbox' => 'الوارد',
    'outbox' => 'الصادر',
    'in_progress' => 'قيد المعالجة',
    'archived_tab' => 'المؤرشفة',
    'all_tab' => 'الكل',

    // Navigation & Resource Labels (New Additions)
    'navigation_group' => 'المراسلات',
    'navigation_label' => 'المراسلات الواردة والصادرة',
    'model_label' => 'رسالة',
    'plural_model_label' => 'المراسلات',

    // ViewRecord Page Additions
    'document_body' => 'نص المعاملة',
    'comments' => 'التعليقات',
    'attached_attachments' => 'المرفقات المصاحبة للمعاملة',
    'forward_to_department' => 'توجيه إلى قسم',
    'note_optional' => 'ملاحظة (اختياري)',
    'forwarded_successfully' => 'تم توجيه المعاملة بنجاح',
    'complete_document' => 'إنهاء المعاملة',
    'document_completed' => 'تم إنهاء المعاملة',
];