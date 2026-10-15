<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('document_routes', function (Blueprint $table) {
            $table->boolean('is_cc')->default(false)->after('to_department_id');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('archive_no');
            $table->foreignId('closed_by')
                ->nullable()
                ->after('closed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['closed_at', 'closed_by']);
        });

        Schema::table('document_routes', function (Blueprint $table) {
            $table->dropColumn('is_cc');
        });
    }
};
