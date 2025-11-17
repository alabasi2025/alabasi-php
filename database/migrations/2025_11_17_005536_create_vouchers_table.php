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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique()->comment('رقم السند');
            $table->enum('voucher_type', ['payment', 'receipt'])->comment('نوع السند: صرف أو قبض');
            $table->enum('payment_method', ['cash', 'bank'])->comment('طريقة الدفع: نقدي أو بنكي');
            $table->date('voucher_date')->comment('تاريخ السند');
            $table->decimal('amount', 15, 2)->comment('المبلغ');
            $table->string('currency', 3)->default('USD')->comment('العملة');
            
            // معلومات المستفيد/الدافع
            $table->string('beneficiary_name')->comment('اسم المستفيد/الدافع');
            $table->unsignedBigInteger('analytical_account_id')->nullable()->comment('الحساب التحليلي');
            
            // معلومات الحساب (صندوق أو بنك)
            $table->unsignedBigInteger('account_id')->comment('الحساب (صندوق/بنك)');
            
            // معلومات إضافية
            $table->text('description')->nullable()->comment('البيان');
            $table->text('notes')->nullable()->comment('ملاحظات');
            
            // معلومات الهيكل التنظيمي
            $table->unsignedBigInteger('unit_id')->nullable()->comment('الوحدة');
            $table->unsignedBigInteger('company_id')->nullable()->comment('المؤسسة');
            $table->unsignedBigInteger('branch_id')->nullable()->comment('الفرع');
            
            // حالة السند
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft')->comment('حالة السند');
            
            // معلومات القيد المحاسبي
            $table->unsignedBigInteger('journal_entry_id')->nullable()->comment('القيد المحاسبي المرتبط');
            
            // معلومات المستخدم
            $table->unsignedBigInteger('created_by')->comment('المستخدم المنشئ');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('المستخدم المعتمد');
            $table->timestamp('approved_at')->nullable()->comment('تاريخ الاعتماد');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('voucher_date');
            $table->index('status');
            $table->index(['voucher_type', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
