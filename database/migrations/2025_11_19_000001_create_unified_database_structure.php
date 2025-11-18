<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * قاعدة بيانات موحدة مع دعم Multi-tenancy
     * تصميم احترافي قابل للتوزيع مستقبلاً
     */
    public function up(): void
    {
        // 1. جدول الوحدات (Units) - القاعدة المركزية + وحدات العمل
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('كود الوحدة الفريد');
            $table->string('name')->comment('اسم الوحدة');
            $table->enum('type', ['central', 'business'])->default('business')->comment('نوع الوحدة');
            $table->boolean('is_active')->default(true)->comment('حالة التفعيل');
            $table->json('settings')->nullable()->comment('إعدادات خاصة بالوحدة');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('type');
            $table->index('is_active');
        });

        // 2. جدول المؤسسات (Companies) - تابعة للوحدات
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('code', 50)->unique()->comment('كود المؤسسة الفريد');
            $table->string('name')->comment('اسم المؤسسة');
            $table->string('tax_number')->nullable()->comment('الرقم الضريبي');
            $table->string('address')->nullable()->comment('العنوان');
            $table->string('phone')->nullable()->comment('الهاتف');
            $table->string('email')->nullable()->comment('البريد الإلكتروني');
            $table->boolean('is_active')->default(true)->comment('حالة التفعيل');
            $table->json('settings')->nullable()->comment('إعدادات خاصة بالمؤسسة');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('unit_id');
            $table->index('is_active');
        });

        // 3. جدول الحسابات (Accounts) - دليل حسابات موحد
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('code', 50)->comment('رقم الحساب');
            $table->string('name')->comment('اسم الحساب');
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('cascade');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->comment('نوع الحساب');
            $table->integer('level')->default(1)->comment('مستوى الحساب');
            $table->boolean('is_final')->default(false)->comment('حساب نهائي');
            $table->boolean('is_active')->default(true)->comment('حالة التفعيل');
            $table->decimal('opening_balance', 15, 2)->default(0)->comment('الرصيد الافتتاحي');
            $table->decimal('current_balance', 15, 2)->default(0)->comment('الرصيد الحالي');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['unit_id', 'company_id', 'code']);
            $table->index(['unit_id', 'company_id']);
            $table->index('type');
            $table->index('is_final');
        });

        // 4. جدول القيود (Journal Entries) - موحد لجميع الوحدات
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('entry_number', 50)->comment('رقم القيد');
            $table->date('entry_date')->comment('تاريخ القيد');
            $table->enum('type', ['opening', 'regular', 'closing', 'adjustment'])->default('regular');
            $table->text('description')->nullable()->comment('البيان');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['draft', 'approved', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['unit_id', 'company_id', 'entry_number']);
            $table->index(['unit_id', 'company_id', 'entry_date']);
            $table->index('status');
        });

        // 5. جدول تفاصيل القيود (Journal Entry Lines)
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['debit', 'credit'])->comment('مدين/دائن');
            $table->decimal('amount', 15, 2)->comment('المبلغ');
            $table->text('description')->nullable()->comment('البيان');
            $table->timestamps();
            
            $table->index('journal_entry_id');
            $table->index('account_id');
        });

        // 6. جدول التحويلات (Clearing Transactions) - محسّن
        Schema::create('clearing_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique()->comment('رقم التحويل');
            $table->date('transaction_date')->comment('تاريخ التحويل');
            
            // من (Source)
            $table->foreignId('from_unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('from_company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('from_account_id')->constrained('accounts')->onDelete('cascade');
            
            // إلى (Destination)
            $table->foreignId('to_unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('to_company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('to_account_id')->constrained('accounts')->onDelete('cascade');
            
            $table->decimal('amount', 15, 2)->comment('المبلغ');
            $table->text('description')->nullable()->comment('البيان');
            $table->enum('type', ['inter_company', 'inter_unit'])->comment('نوع التحويل');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            
            // القيود المرتبطة
            $table->foreignId('source_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            $table->foreignId('destination_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['from_unit_id', 'from_company_id']);
            $table->index(['to_unit_id', 'to_company_id']);
            $table->index('status');
            $table->index('transaction_date');
        });

        // 7. جدول المستخدمين المحدث (إضافة حقول جديدة)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('unit_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'accountant', 'viewer'])->default('viewer')->after('email');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
        });

        // 8. جدول سجل النشاطات (Activity Log) - للأمان والمراجعة
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action')->comment('نوع العملية');
            $table->string('model_type')->nullable()->comment('نوع النموذج');
            $table->unsignedBigInteger('model_id')->nullable()->comment('معرف النموذج');
            $table->json('old_values')->nullable()->comment('القيم القديمة');
            $table->json('new_values')->nullable()->comment('القيم الجديدة');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });

        // 9. جدول الإعدادات (Settings) - إعدادات النظام
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('key')->comment('مفتاح الإعداد');
            $table->text('value')->nullable()->comment('قيمة الإعداد');
            $table->string('type')->default('string')->comment('نوع البيانات');
            $table->text('description')->nullable()->comment('الوصف');
            $table->timestamps();
            
            $table->unique(['unit_id', 'company_id', 'key']);
        });

        // 10. جدول النسخ الاحتياطية (Backups) - ميزة احترافية
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->comment('اسم الملف');
            $table->string('path')->comment('المسار');
            $table->bigInteger('size')->comment('الحجم بالبايت');
            $table->enum('type', ['full', 'incremental'])->default('full');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn(['unit_id', 'company_id', 'role', 'is_active', 'last_login_at']);
        });
        
        Schema::dropIfExists('clearing_transactions');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('units');
    }
};
