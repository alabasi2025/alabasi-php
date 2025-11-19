<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Main\Unit;
use App\Models\Main\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class HodeidahUnitSeeder extends Seeder
{
    /**
     * إنشاء وحدة أعمال الحديدة مع المؤسسات والمستخدمين
     */
    public function run(): void
    {
        $this->command->info('🚀 بدء إنشاء وحدة أعمال الحديدة...');

        // 1. إنشاء وحدة أعمال الحديدة
        $hodeidahUnit = Unit::firstOrCreate(
            ['code' => 'HODEIDAH'],
            [
                'name' => 'وحدة أعمال الحديدة',
                'description' => 'وحدة أعمال الحديدة - تحتوي على جميع مؤسسات وفروع الحديدة',
                'database_name' => 'u306850950_alabasi_unit_2',
                'is_active' => true,
                'is_development' => false,
            ]
        );

        $this->command->info('✅ تم إنشاء وحدة أعمال الحديدة');

        // 2. إنشاء مؤسسة افتراضية في وحدة الحديدة
        $hodeidahCompany = Company::firstOrCreate(
            [
                'unit_id' => $hodeidahUnit->id,
                'code' => 'HOD001',
            ],
            [
                'name' => 'مؤسسة الحديدة الرئيسية',
                'description' => 'المؤسسة الرئيسية لوحدة أعمال الحديدة',
                'tax_number' => '1000000001',
                'commercial_register' => 'CR-HOD-001',
                'phone' => '+967-3-200000',
                'email' => 'info@hodeidah.alabasi.es',
                'address' => 'الحديدة، اليمن',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ تم إنشاء مؤسسة الحديدة الرئيسية');

        // 3. إنشاء مستخدمين لوحدة الحديدة
        $hodeidahUsers = [
            [
                'name' => 'مدير وحدة الحديدة',
                'email' => 'hodeidah.admin@alabasi.es',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'محاسب الحديدة',
                'email' => 'hodeidah.accountant@alabasi.es',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($hodeidahUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✅ تم إنشاء مستخدمي وحدة الحديدة');

        $this->command->info('');
        $this->command->info('🎉 تم إكمال إنشاء وحدة أعمال الحديدة بنجاح!');
        $this->command->info('');
        $this->command->info('📋 معلومات الوحدة:');
        $this->command->info('   الكود: HODEIDAH');
        $this->command->info('   الاسم: وحدة أعمال الحديدة');
        $this->command->info('   قاعدة البيانات: u306850950_alabasi_unit_2');
        $this->command->info('');
        $this->command->info('📋 معلومات المؤسسة:');
        $this->command->info('   الكود: HOD001');
        $this->command->info('   الاسم: مؤسسة الحديدة الرئيسية');
        $this->command->info('');
        $this->command->info('📋 بيانات الدخول:');
        $this->command->info('   المدير: hodeidah.admin@alabasi.es / password');
        $this->command->info('   المحاسب: hodeidah.accountant@alabasi.es / password');
    }
}
