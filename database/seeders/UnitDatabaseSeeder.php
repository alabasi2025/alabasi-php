<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit\Company;
use App\Models\Unit\Branch;
use App\Models\Unit\Account;
use Illuminate\Support\Facades\DB;

class UnitDatabaseSeeder extends Seeder
{
    /**
     * The command instance.
     *
     * @var \Illuminate\Console\Command|null
     */
    protected $command;

    /**
     * Set the command instance.
     *
     * @param \Illuminate\Console\Command $command
     * @return void
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }
    /**
     * Run the database seeds.
     *
     * @param string $connection اسم الـ connection (unit_2 أو unit_3)
     * @param int $companyId معرف المؤسسة في القاعدة المركزية
     * @param array $companyData بيانات المؤسسة
     */
    public function run(string $connection = 'unit_2', int $companyId = 1, array $companyData = []): void
    {
        DB::connection($connection)->beginTransaction();

        try {
            // إنشاء المؤسسة في قاعدة بيانات الوحدة
            $company = Company::on($connection)->create([
                'name' => $companyData['name'] ?? 'أعمال الموظفين',
                'code' => $companyData['code'] ?? 'COMP_EMPLOYEES',
                'description' => $companyData['description'] ?? 'مؤسسة أعمال الموظفين',
                'legal_name' => $companyData['legal_name'] ?? 'شركة أعمال الموظفين المحدودة',
                'tax_number' => $companyData['tax_number'] ?? '300000000000003',
                'commercial_registration' => $companyData['commercial_registration'] ?? '1010000000',
                'address' => $companyData['address'] ?? 'الحديدة، اليمن',
                'phone' => $companyData['phone'] ?? '+967 777 000 000',
                'email' => $companyData['email'] ?? 'info@employees.alabasi.es',
                'fiscal_year_start' => $companyData['fiscal_year_start'] ?? '2025-01-01',
                'fiscal_year_end' => $companyData['fiscal_year_end'] ?? '2025-12-31',
                'currency' => 'SAR',
                'is_active' => true,
            ]);

            // إنشاء الفرع
            $branch = Branch::on($connection)->create([
                'company_id' => $company->id,
                'name' => 'فرع الحديدة',
                'code' => 'BR' . str_pad($companyId, 3, '0', STR_PAD_LEFT),
                'address' => 'الحديدة، شارع الكورنيش',
                'phone' => '+967 777 000 001',
                'manager_name' => 'أحمد محمد',
                'is_active' => true,
            ]);

            // إنشاء الحسابات الأساسية
            $this->createBasicAccounts($connection, $company->id);

            DB::connection($connection)->commit();

            $this->command->info("✅ تم إنشاء بيانات المؤسسة في {$connection} بنجاح!");
            $this->command->info('🏢 المؤسسة: ' . $company->name);
            $this->command->info('🏪 الفروع: ' . Branch::on($connection)->count());
            $this->command->info('💰 الحسابات: ' . Account::on($connection)->count());

        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();
            $this->command->error('❌ خطأ في إنشاء البيانات: ' . $e->getMessage());
        }
    }

    /**
     * Create basic accounts for the company.
     *
     * @param string $connection
     * @param int $companyId
     * @return void
     */
    protected function createBasicAccounts(string $connection, int $companyId): void
    {
        $accounts = [
            // الأصول
            [
                'account_number' => '1000',
                'account_name' => 'الأصول',
                'account_type' => 'asset',
                'account_nature' => 'debit',
                'level' => 1,
                'parent_account_id' => null,
            ],
            [
                'account_number' => '1100',
                'account_name' => 'الأصول المتداولة',
                'account_type' => 'asset',
                'account_nature' => 'debit',
                'level' => 2,
                'parent_account_id' => null, // سيتم تحديثه
            ],
            [
                'account_number' => '1110',
                'account_name' => 'الصندوق',
                'account_type' => 'asset',
                'account_nature' => 'debit',
                'level' => 3,
                'parent_account_id' => null, // سيتم تحديثه
            ],
            [
                'account_number' => '1120',
                'account_name' => 'البنك',
                'account_type' => 'asset',
                'account_nature' => 'debit',
                'level' => 3,
                'parent_account_id' => null, // سيتم تحديثه
            ],

            // الخصوم
            [
                'account_number' => '2000',
                'account_name' => 'الخصوم',
                'account_type' => 'liability',
                'account_nature' => 'credit',
                'level' => 1,
                'parent_account_id' => null,
            ],
            [
                'account_number' => '2100',
                'account_name' => 'الخصوم المتداولة',
                'account_type' => 'liability',
                'account_nature' => 'credit',
                'level' => 2,
                'parent_account_id' => null, // سيتم تحديثه
            ],

            // حقوق الملكية
            [
                'account_number' => '3000',
                'account_name' => 'حقوق الملكية',
                'account_type' => 'equity',
                'account_nature' => 'credit',
                'level' => 1,
                'parent_account_id' => null,
            ],
            [
                'account_number' => '3100',
                'account_name' => 'رأس المال',
                'account_type' => 'equity',
                'account_nature' => 'credit',
                'level' => 2,
                'parent_account_id' => null, // سيتم تحديثه
            ],

            // الإيرادات
            [
                'account_number' => '4000',
                'account_name' => 'الإيرادات',
                'account_type' => 'revenue',
                'account_nature' => 'credit',
                'level' => 1,
                'parent_account_id' => null,
            ],
            [
                'account_number' => '4100',
                'account_name' => 'إيرادات المبيعات',
                'account_type' => 'revenue',
                'account_nature' => 'credit',
                'level' => 2,
                'parent_account_id' => null, // سيتم تحديثه
            ],

            // المصروفات
            [
                'account_number' => '5000',
                'account_name' => 'المصروفات',
                'account_type' => 'expense',
                'account_nature' => 'debit',
                'level' => 1,
                'parent_account_id' => null,
            ],
            [
                'account_number' => '5100',
                'account_name' => 'مصروفات التشغيل',
                'account_type' => 'expense',
                'account_nature' => 'debit',
                'level' => 2,
                'parent_account_id' => null, // سيتم تحديثه
            ],
        ];

        $createdAccounts = [];

        foreach ($accounts as $accountData) {
            $account = Account::on($connection)->create([
                'company_id' => $companyId,
                'account_number' => $accountData['account_number'],
                'account_name' => $accountData['account_name'],
                'account_type' => $accountData['account_type'],
                'account_nature' => $accountData['account_nature'],
                'level' => $accountData['level'],
                'is_active' => true,
                'is_system' => true,
            ]);

            $createdAccounts[$accountData['account_number']] = $account;
        }

        // تحديث العلاقات الهرمية
        $createdAccounts['1100']->update(['parent_account_id' => $createdAccounts['1000']->id]);
        $createdAccounts['1110']->update(['parent_account_id' => $createdAccounts['1100']->id]);
        $createdAccounts['1120']->update(['parent_account_id' => $createdAccounts['1100']->id]);

        $createdAccounts['2100']->update(['parent_account_id' => $createdAccounts['2000']->id]);

        $createdAccounts['3100']->update(['parent_account_id' => $createdAccounts['3000']->id]);

        $createdAccounts['4100']->update(['parent_account_id' => $createdAccounts['4000']->id]);

        $createdAccounts['5100']->update(['parent_account_id' => $createdAccounts['5000']->id]);
    }
}
