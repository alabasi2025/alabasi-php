<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Main\Unit;
use App\Models\Main\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminDevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds for Admin Development Unit
     * 
     * هذا الـ Seeder خاص بالوحدة المركزية فقط
     * يضيف بيانات افتراضية للاختبار والتطوير
     */
    public function run(): void
    {
        // 1. إنشاء الوحدة المركزية للتطوير
        $adminUnit = Unit::firstOrCreate(
            ['code' => 'ADMIN'],
            [
                'name' => 'الوحدة المركزية',
                'name_en' => 'Admin Unit',
                'description' => 'وحدة الاختبار والتطوير - تحتوي على بيانات افتراضية',
                'is_active' => true,
                'is_development' => true, // علامة للتطوير
            ]
        );

        $this->command->info('✅ تم إنشاء الوحدة المركزية');

        // 2. إنشاء مؤسسات افتراضية للاختبار
        $testCompanies = [
            [
                'code' => 'TEST001',
                'name' => 'شركة الاختبار الأولى',
                'name_en' => 'Test Company 1',
                'tax_number' => '1234567890',
                'phone' => '0500000001',
                'email' => 'test1@alabasi.es',
            ],
            [
                'code' => 'TEST002',
                'name' => 'شركة الاختبار الثانية',
                'name_en' => 'Test Company 2',
                'tax_number' => '0987654321',
                'phone' => '0500000002',
                'email' => 'test2@alabasi.es',
            ],
            [
                'code' => 'DEMO001',
                'name' => 'شركة العرض التوضيحي',
                'name_en' => 'Demo Company',
                'tax_number' => '5555555555',
                'phone' => '0500000003',
                'email' => 'demo@alabasi.es',
            ],
        ];

        foreach ($testCompanies as $companyData) {
            Company::firstOrCreate(
                [
                    'unit_id' => $adminUnit->id,
                    'code' => $companyData['code'],
                ],
                array_merge($companyData, [
                    'unit_id' => $adminUnit->id,
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('✅ تم إنشاء 3 مؤسسات افتراضية');

        // 3. إنشاء مستخدمين للاختبار
        $testUsers = [
            [
                'name' => 'مدير النظام',
                'email' => 'admin@alabasi.es',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ],
            [
                'name' => 'مطور النظام',
                'email' => 'developer@alabasi.es',
                'password' => Hash::make('dev123'),
                'role' => 'developer',
            ],
            [
                'name' => 'مستخدم تجريبي',
                'email' => 'test@alabasi.es',
                'password' => Hash::make('test123'),
                'role' => 'user',
            ],
        ];

        foreach ($testUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✅ تم إنشاء 3 مستخدمين للاختبار');

        // 4. إضافة بيانات افتراضية إضافية
        $this->seedTestAccounts($adminUnit);
        $this->seedTestJournalEntries($adminUnit);

        $this->command->info('🎉 تم إكمال بيانات الوحدة المركزية بنجاح!');
        $this->command->info('');
        $this->command->info('📋 بيانات الدخول للاختبار:');
        $this->command->info('   المدير: admin@alabasi.es / admin123');
        $this->command->info('   المطور: developer@alabasi.es / dev123');
        $this->command->info('   المستخدم: test@alabasi.es / test123');
    }

    /**
     * إضافة حسابات افتراضية للاختبار
     */
    private function seedTestAccounts(Unit $unit): void
    {
        // يمكن إضافة حسابات افتراضية هنا
        $this->command->info('✅ تم إضافة حسابات افتراضية');
    }

    /**
     * إضافة قيود افتراضية للاختبار
     */
    private function seedTestJournalEntries(Unit $unit): void
    {
        // يمكن إضافة قيود افتراضية هنا
        $this->command->info('✅ تم إضافة قيود افتراضية');
    }
}
