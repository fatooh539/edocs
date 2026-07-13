<?php

use App\enums\priority;
use App\enums\status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();
            $table->string('name');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('job_title')->nullable();

            $table->string('phone')->nullable();

        });

        Schema::create('documents', function (Blueprint $table) {

            $table->id();

            $table->string('document_no')->unique();

            $table->string('subject');

            $table->longText('body')->nullable();

            $table->foreignId('sender_department_id')
                ->constrained('departments');

            $table->foreignId('created_by')
                ->constrained('users');

            $table->foreignId('current_department_id')
                ->constrained('departments');

            // $table->enum('priority', priority::cases())->default('NORMAL');
            $table->string('priority', 20)->default(Priority::NORMAL->value);

            // $table->enum('status', status::cases())->default('DRAFT');
            $table->string('status', 20)->default(status::DRAFT->value);

            $table->boolean('confidential')->default(false);

            $table->date('document_date')->nullable();

            $table->string('archive_no')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('document_routes', function (Blueprint $table) {


            $table->id();
            $table->uuid()->unique();

            $table->foreignId('parent_route_id')
                ->nullable()
                ->constrained('document_routes')
                ->nullOnDelete();

            $table->foreignId('document_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('from_department_id')
                ->nullable()
                ->constrained('departments');

            $table->foreignId('to_department_id')
                ->nullable()
                ->constrained('departments');

            $table->foreignId('sent_by')
                ->nullable()
                ->constrained('users');
            
            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users');


            $table->string('route_status', 20)->default(status::DRAFT->value);


            $table->timestamp('sent_at')->nullable();

            $table->timestamp('received_at')->nullable();

            $table->timestamp('completed_at')->nullable();


            $table->timestamps();
            $table->softDeletes();
        });



        Schema::create('document_comments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('document_route_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained();

            $table->text('comment');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_attachments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('document_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('file_name');          // اسم التخزين

            $table->string('original_name');      // الاسم الحقيقي

            $table->string('file_path');

            $table->string('disk')->default('public');

            $table->string('mime_type', 100)->nullable();

            $table->string('extension', 20)->nullable();

            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('checksum', 64)->nullable();

            $table->unsignedBigInteger('download_count')->default(0);

            $table->foreignId('uploaded_by')
                ->constrained('users');

            $table->timestamps();

            $table->softDeletes();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('document_comments');
        Schema::dropIfExists('document_routes');
        Schema::dropIfExists('documents');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'job_title', 'phone']);
        });

        Schema::dropIfExists('departments');
    }
};