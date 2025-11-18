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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            
            // معلومات الحساب
            $table->string('account_number')->unique();
            $table->string('account_name');
            $table->text('description')->nullable();
            
            // نوع الحساب
            $table->enum('account_type', [
                'asset',          // أصول
                'liability',      // خصوم
                'equity',         // حقوق ملكية
                'revenue',        // إيرادات
                'expense',        // مصروفات
                'clearing'        // حسابات وسيطة
            ]);
            
            // نوع الحساب الوسيط (إذا كان clearing)
            $table->enum('clearing_type', [
                'inter_company',  // بين مؤسسات في نفس الوحدة
                'inter_unit'      // بين وحدات مختلفة
            ])->nullable();
            
            // معلومات الحساب الوسيط المرتبط
            $table->unsignedBigInteger('related_unit_id')->nullable(); // للحسابات بين الوحدات
            $table->unsignedBigInteger('related_company_id')->nullable(); // للحسابات بين المؤسسات
            
            // طبيعة الحساب
            $table->enum('account_nature', ['debit', 'credit']);
            
            // التسلسل الهرمي
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->integer('level')->default(1);
            
            // الحالة
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // حسابات النظام لا يمكن حذفها
            
            $table->timestamps();
            
            // الفهارس
            $table->index('company_id');
            $table->index('account_type');
            $table->index('clearing_type');
            $table->index(['related_unit_id', 'related_company_id']);
            $table->index('parent_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
