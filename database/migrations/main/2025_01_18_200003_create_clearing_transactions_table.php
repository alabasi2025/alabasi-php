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
        Schema::connection('main')->create('clearing_transactions', function (Blueprint $table) {
            $table->id();
            
            // نوع التحويل: inter_company (بين مؤسسات) أو inter_unit (بين وحدات)
            $table->enum('transaction_type', ['inter_company', 'inter_unit']);
            
            // معلومات المصدر
            $table->unsignedBigInteger('source_unit_id');
            $table->unsignedBigInteger('source_company_id');
            $table->unsignedBigInteger('source_journal_entry_id')->nullable();
            
            // معلومات الهدف
            $table->unsignedBigInteger('target_unit_id');
            $table->unsignedBigInteger('target_company_id');
            $table->unsignedBigInteger('target_journal_entry_id')->nullable();
            
            // تفاصيل التحويل
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            
            // الحالة: pending, completed, cancelled
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            
            // المستخدم الذي أنشأ التحويل
            $table->unsignedBigInteger('created_by')->nullable();
            
            // تاريخ الاعتماد
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            $table->timestamps();
            
            // الفهارس
            $table->index(['source_unit_id', 'source_company_id']);
            $table->index(['target_unit_id', 'target_company_id']);
            $table->index('status');
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('main')->dropIfExists('clearing_transactions');
    }
};
