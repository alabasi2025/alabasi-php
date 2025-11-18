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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            
            // معلومات القيد
            $table->string('entry_number')->unique();
            $table->date('entry_date');
            $table->text('description');
            
            // نوع القيد
            $table->enum('entry_type', [
                'opening',        // قيد افتتاحي
                'regular',        // قيد عادي
                'adjustment',     // قيد تسوية
                'closing',        // قيد إقفال
                'clearing'        // قيد تحويل (وسيط)
            ])->default('regular');
            
            // معلومات التحويل (إذا كان clearing)
            $table->unsignedBigInteger('clearing_transaction_id')->nullable();
            
            // الحالة
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            
            // معلومات المستخدم
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            
            $table->timestamps();
            
            // الفهارس
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('entry_date');
            $table->index('entry_type');
            $table->index('status');
            $table->index('clearing_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
