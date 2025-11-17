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
        // 1. إنشاء جدول account_types (أنواع الحسابات)
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code')->comment('رمز نوع الحساب');
            $table->string('name')->comment('اسم نوع الحساب');
            $table->enum('nature', ['debit', 'credit'])->comment('طبيعة الحساب (مدين/دائن)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: كل مؤسسة لها أكواد خاصة بها
            $table->unique(['company_id', 'code']);
            
            // Indexes
            $table->index('company_id');
        });

        // 2. تحديث جدول accounts
        Schema::table('accounts', function (Blueprint $table) {
            // إضافة company_id إذا لم يكن موجوداً
            if (!Schema::hasColumn('accounts', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            
            // إضافة account_type_id بدلاً من enum
            if (!Schema::hasColumn('accounts', 'account_type_id')) {
                $table->foreignId('account_type_id')->nullable()->after('company_id')->constrained()->onDelete('restrict');
            }
            
            // إضافة parent_id للهيكلية الشجرية
            if (!Schema::hasColumn('accounts', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('account_type_id')->constrained('accounts')->onDelete('cascade');
            }
            
            // إضافة المستوى
            if (!Schema::hasColumn('accounts', 'level')) {
                $table->integer('level')->default(1)->after('account_name');
            }
            
            // إضافة حالة النشاط
            if (!Schema::hasColumn('accounts', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('level');
            }
            
            // إضافة هل الحساب رئيسي أم فرعي
            if (!Schema::hasColumn('accounts', 'is_main')) {
                $table->boolean('is_main')->default(false)->after('is_active')->comment('هل الحساب رئيسي (للترتيب فقط) أم فرعي (يمكن الترحيل عليه)');
            }
            
            // إضافة الوصف
            if (!Schema::hasColumn('accounts', 'description')) {
                $table->text('description')->nullable()->after('is_final');
            }
            
            // إضافة ملاحظات
            if (!Schema::hasColumn('accounts', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });

        // 3. إنشاء جدول analytical_account_types (أنواع الحسابات التحليلية)
        Schema::create('analytical_account_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code')->comment('رمز النوع التحليلي');
            $table->string('name')->comment('اسم النوع التحليلي (صندوق، بنك، صراف، محفظة، مورد، عميل)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['company_id', 'code']);
            
            // Indexes
            $table->index('company_id');
        });

        // 4. إضافة analytical_account_type_id إلى جدول accounts (فقط للحسابات الفرعية)
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'analytical_account_type_id')) {
                $table->foreignId('analytical_account_type_id')->nullable()->after('account_type_id')->constrained()->onDelete('set null')->comment('النوع التحليلي للحساب (فقط للحسابات الفرعية)');
            }
        });

        // 5. إنشاء جدول analytical_accounts (الحسابات التحليلية الفعلية)
        Schema::create('analytical_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('analytical_account_type_id')->constrained()->onDelete('restrict')->comment('نوع الحساب التحليلي (صندوق، بنك، إلخ)');
            $table->foreignId('account_id')->constrained()->onDelete('cascade')->comment('الحساب الفرعي المرتبط من الدليل');
            $table->string('code')->comment('رمز الحساب التحليلي');
            $table->string('name')->comment('اسم الحساب التحليلي');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['company_id', 'code']);
            
            // Indexes
            $table->index('company_id');
            $table->index('analytical_account_type_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف جدول analytical_accounts
        Schema::dropIfExists('analytical_accounts');
        
        // حذف analytical_account_type_id من accounts
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'analytical_account_type_id')) {
                $table->dropForeign(['accounts_analytical_account_type_id_foreign']);
                $table->dropColumn('analytical_account_type_id');
            }
        });
        
        // حذف جدول analytical_account_types
        Schema::dropIfExists('analytical_account_types');
        
        // حذف الحقول المضافة من accounts
        Schema::table('accounts', function (Blueprint $table) {
            $columns = ['company_id', 'account_type_id', 'parent_id', 'level', 'is_active', 'is_main', 'description', 'notes'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('accounts', $column)) {
                    if (in_array($column, ['company_id', 'account_type_id', 'parent_id'])) {
                        $table->dropForeign(['accounts_' . $column . '_foreign']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
        
        // حذف جدول account_types
        Schema::dropIfExists('account_types');
    }
};
