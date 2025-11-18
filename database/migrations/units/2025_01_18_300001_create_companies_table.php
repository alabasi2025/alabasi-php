<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * هذا الجدول يتم إنشاؤه في قاعدة بيانات كل وحدة
     * ويحتوي على التفاصيل الكاملة للمؤسسات
     */
    public function up(): void
    {
        // سيتم تشغيل هذا على connection محدد عند الاستدعاء
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            
            // معلومات الشركة
            $table->string('legal_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('commercial_registration')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // الإعدادات المحاسبية
            $table->date('fiscal_year_start')->nullable();
            $table->date('fiscal_year_end')->nullable();
            $table->string('currency', 3)->default('SAR');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
