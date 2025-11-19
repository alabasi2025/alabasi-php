<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CentralUnitSeeder extends Seeder
{
    /**
     * Run the database seeds for Central Unit
     * 
     * هذا الـ Seeder خاص بالوحدة المركزية
     * يضيف الوحدة المركزية + مؤسسة + مستخدم مدير
     */
    public function run(): void
    {
        // استخدام الاتصال الافتراضي
        DB::transaction(function () {
            // 1. التحقق من وجود الوحدة المركزية
            $centralUnit = DB::table('units')
                ->where('code', 'CENTRAL')
                ->first();

            if (!$centralUnit) {
                // إنشاء الوحدة المركزية
                $unitId = DB::table('units')->insertGetId([
                    'code' => 'CENTRAL',
                    'name' => 'الوحدة المركزية',
                    'description' => 'الوحدة المركزية - للإدارة العامة والإشراف على جميع الوحدات',
                    'database_name' => 'u306850950_alabasi_main',
                    'is_active' => 1,
                    'is_development' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info('✅ تم إنشاء الوحدة المركزية (ID: ' . $unitId . ')');
            } else {
                $unitId = $centralUnit->id;
                $this->command->info('ℹ️ الوحدة المركزية موجودة مسبقاً (ID: ' . $unitId . ')');
            }

            // 2. التحقق من وجود مؤسسة للوحدة المركزية
            $centralCompany = DB::table('companies')
                ->where('unit_id', $unitId)
                ->where('company_code', 'CENTRAL001')
                ->first();

            if (!$centralCompany) {
                // إنشاء مؤسسة للوحدة المركزية
                $companyId = DB::table('companies')->insertGetId([
                    'unit_id' => $unitId,
                    'company_code' => 'CENTRAL001',
                    'company_name' => 'المؤسسة المركزية',
                    'company_name_en' => 'Central Organization',
                    'tax_number' => '9999999999',
                    'phone' => '0500000000',
                    'email' => 'central@alabasi.es',
                    'address' => 'المقر الرئيسي',
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info('✅ تم إنشاء المؤسسة المركزية (ID: ' . $companyId . ')');
            } else {
                $companyId = $centralCompany->id;
                $this->command->info('ℹ️ المؤسسة المركزية موجودة مسبقاً (ID: ' . $companyId . ')');
            }

            // 3. التحقق من وجود مستخدم مدير للوحدة المركزية
            $adminUser = DB::table('users')
                ->where('email', 'admin@alabasi.es')
                ->first();

            if (!$adminUser) {
                // إنشاء مستخدم مدير
                $userId = DB::table('users')->insertGetId([
                    'name' => 'مدير النظام المركزي',
                    'email' => 'admin@alabasi.es',
                    'password' => Hash::make('Alabasi@2025'),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info('✅ تم إنشاء مستخدم المدير (ID: ' . $userId . ')');
            } else {
                $userId = $adminUser->id;
                $this->command->info('ℹ️ مستخدم المدير موجود مسبقاً (ID: ' . $userId . ')');
            }

            // 4. عرض معلومات الدخول
            $this->command->info('');
            $this->command->info('═══════════════════════════════════════════════════════');
            $this->command->info('🎉 تم إعداد الوحدة المركزية بنجاح!');
            $this->command->info('═══════════════════════════════════════════════════════');
            $this->command->info('');
            $this->command->info('📋 بيانات الدخول:');
            $this->command->info('   🔹 الوحدة: الوحدة المركزية');
            $this->command->info('   🔹 المؤسسة: المؤسسة المركزية');
            $this->command->info('   🔹 البريد: admin@alabasi.es');
            $this->command->info('   🔹 كلمة المرور: Alabasi@2025');
            $this->command->info('');
            $this->command->info('═══════════════════════════════════════════════════════');
        });
    }
}
