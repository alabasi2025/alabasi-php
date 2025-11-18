<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * إنشاء البيانات الأولية للنظام
     */
    public function run(): void
    {
        // 1. إنشاء القاعدة المركزية
        $centralUnit = Unit::create([
            'code' => 'CENTRAL',
            'name' => 'القاعدة المركزية',
            'type' => 'central',
            'is_active' => true,
            'settings' => [
                'can_view_all_units' => true,
                'can_manage_transfers' => true,
            ],
        ]);

        // 2. إنشاء وحدة أعمال الحديدة
        $hodeidahUnit = Unit::create([
            'code' => 'HODEIDAH',
            'name' => 'أعمال الحديدة',
            'type' => 'business',
            'is_active' => true,
            'settings' => [],
        ]);

        // 3. إنشاء وحدة أعمال العباسي
        $alabasiUnit = Unit::create([
            'code' => 'ALABASI',
            'name' => 'أعمال العباسي',
            'type' => 'business',
            'is_active' => true,
            'settings' => [],
        ]);

        // 4. إنشاء مؤسسات لوحدة الحديدة
        $hodeidahCompany1 = Company::create([
            'unit_id' => $hodeidahUnit->id,
            'code' => 'HOD-001',
            'name' => 'مؤسسة الحديدة الأولى',
            'tax_number' => '1001',
            'address' => 'الحديدة، اليمن',
            'phone' => '+967-3-123456',
            'email' => 'info@hodeidah1.com',
            'is_active' => true,
        ]);

        $hodeidahCompany2 = Company::create([
            'unit_id' => $hodeidahUnit->id,
            'code' => 'HOD-002',
            'name' => 'مؤسسة الحديدة الثانية',
            'tax_number' => '1002',
            'address' => 'الحديدة، اليمن',
            'phone' => '+967-3-123457',
            'email' => 'info@hodeidah2.com',
            'is_active' => true,
        ]);

        // 5. إنشاء مؤسسات لوحدة العباسي
        $alabasiCompany1 = Company::create([
            'unit_id' => $alabasiUnit->id,
            'code' => 'ALB-001',
            'name' => 'مؤسسة العباسي الأولى',
            'tax_number' => '2001',
            'address' => 'صنعاء، اليمن',
            'phone' => '+967-1-123456',
            'email' => 'info@alabasi1.com',
            'is_active' => true,
        ]);

        $alabasiCompany2 = Company::create([
            'unit_id' => $alabasiUnit->id,
            'code' => 'ALB-002',
            'name' => 'مؤسسة العباسي الثانية',
            'tax_number' => '2002',
            'address' => 'صنعاء، اليمن',
            'phone' => '+967-1-123457',
            'email' => 'info@alabasi2.com',
            'is_active' => true,
        ]);

        // 6. إنشاء مستخدم مدير للقاعدة المركزية
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@alabasi.es',
            'password' => Hash::make('Alabasi@2025'),
            'unit_id' => $centralUnit->id,
            'company_id' => null,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 7. إنشاء مستخدم محاسب لوحدة الحديدة
        User::create([
            'name' => 'محاسب الحديدة',
            'email' => 'accountant@hodeidah.com',
            'password' => Hash::make('password123'),
            'unit_id' => $hodeidahUnit->id,
            'company_id' => $hodeidahCompany1->id,
            'role' => 'accountant',
            'is_active' => true,
        ]);

        // 8. إنشاء مستخدم محاسب لوحدة العباسي
        User::create([
            'name' => 'محاسب العباسي',
            'email' => 'accountant@alabasi.com',
            'password' => Hash::make('password123'),
            'unit_id' => $alabasiUnit->id,
            'company_id' => $alabasiCompany1->id,
            'role' => 'accountant',
            'is_active' => true,
        ]);

        $this->command->info('✅ تم إنشاء البيانات الأولية بنجاح!');
        $this->command->info('📊 الإحصائيات:');
        $this->command->info('   - الوحدات: ' . Unit::count());
        $this->command->info('   - المؤسسات: ' . Company::count());
        $this->command->info('   - المستخدمين: ' . User::count());
        $this->command->info('');
        $this->command->info('🔐 بيانات تسجيل الدخول:');
        $this->command->info('   Email: admin@alabasi.es');
        $this->command->info('   Password: Alabasi@2025');
    }
}
