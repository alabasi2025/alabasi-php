<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('main')->create('backup_logs', function (Blueprint $table) {
            $table->id();
            
            // نوع النسخة: daily_company, weekly_unit, monthly_full
            $table->enum('backup_type', ['daily_company', 'weekly_unit', 'monthly_full']);
            
            // معلومات الوحدة والمؤسسة (null للنسخة الكاملة)
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            
            // معلومات الملف
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size'); // بالبايت
            
            // الحالة: success, failed
            $table->enum('status', ['success', 'failed']);
            $table->text('error_message')->nullable();
            
            // تاريخ الحذف التلقائي
            $table->timestamp('auto_delete_at')->nullable();
            
            $table->timestamps();
            
            // الفهارس
            $table->index(['unit_id', 'company_id']);
            $table->index('backup_type');
            $table->index('status');
            $table->index('auto_delete_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('main')->dropIfExists('backup_logs');
    }
};
