<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Main\Unit;
use App\Models\Main\Company;
use Illuminate\Support\Facades\DB;

class MainDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::connection('main')->beginTransaction();

        try {
            // إنشاء الوحدات
            $unitHodeidah = Unit::create([
                'name' => 'أعمال الحديدة',
                'code' => 'UNIT_HODEIDAH',
                'description' => 'وحدة أعمال الحديدة - تشمل جميع المؤسسات والفروع في الحديدة',
                'database_name' => 'unit_2',
                'is_active' => true,
            ]);

            $unitAlabassi = Unit::create([
                'name' => 'أعمال العباسي',
                'code' => 'UNIT_ALABASSI',
                'description' => 'وحدة أعمال العباسي - تشمل جميع المؤسسات والفروع في العباسي',
                'database_name' => 'unit_3',
                'is_active' => true,
            ]);

            // إنشاء المؤسسات في وحدة الحديدة
            Company::create([
                'unit_id' => $unitHodeidah->id,
                'name' => 'أعمال الموظفين',
                'code' => 'COMP_EMPLOYEES',
                'description' => 'مؤسسة أعمال الموظفين',
                'is_active' => true,
            ]);

            Company::create([
                'unit_id' => $unitHodeidah->id,
                'name' => 'أعمال المحاسب',
                'code' => 'COMP_ACCOUNTANT',
                'description' => 'مؤسسة أعمال المحاسب',
                'is_active' => true,
            ]);

            Company::create([
                'unit_id' => $unitHodeidah->id,
                'name' => 'الأنظمة',
                'code' => 'COMP_SYSTEMS',
                'description' => 'مؤسسة الأنظمة',
                'is_active' => true,
            ]);

            // إنشاء المؤسسات في وحدة العباسي
            Company::create([
                'unit_id' => $unitAlabassi->id,
                'name' => 'النقدية',
                'code' => 'COMP_CASH',
                'description' => 'مؤسسة النقدية',
                'is_active' => true,
            ]);

            DB::connection('main')->commit();

            $this->command->info('✅ تم إنشاء بيانات القاعدة المركزية بنجاح!');
            $this->command->info('📊 الوحدات: ' . Unit::count());
            $this->command->info('🏢 المؤسسات: ' . Company::count());

        } catch (\Exception $e) {
            DB::connection('main')->rollBack();
            $this->command->error('❌ خطأ في إنشاء البيانات: ' . $e->getMessage());
        }
    }
}
