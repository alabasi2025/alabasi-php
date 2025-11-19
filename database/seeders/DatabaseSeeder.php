<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 بدء تعبئة قاعدة البيانات...');
        $this->command->info('');

        // تشغيل Seeder الوحدة المركزية (للاختبار والتطوير فقط)
        if (app()->environment(['local', 'development'])) {
            $this->command->info('🔧 بيئة التطوير: تشغيل بيانات الاختبار...');
            $this->call(AdminDevelopmentSeeder::class);
        } else {
            $this->command->warn('⚠️  بيئة الإنتاج: تخطي بيانات الاختبار');
        }

        $this->command->info('');
        $this->command->info('✅ تم إكمال تعبئة قاعدة البيانات بنجاح!');
    }
}
