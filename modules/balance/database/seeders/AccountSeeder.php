<?php

namespace WuriN7i\Balance\Database\Seeders;

use Illuminate\Database\Seeder;
use WuriN7i\Balance\Enums\AccountBehavior;
use WuriN7i\Balance\Enums\AccountCategory;
use WuriN7i\Balance\Models\Account;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // --- ASSETS ---
            [
                'code' => '101',
                'name' => 'Kas',
                'category' => AccountCategory::Assets,
                'account_behavior' => AccountBehavior::FLEXIBLE,
            ],
            [
                'code' => '102',
                'name' => 'Bank',
                'category' => AccountCategory::Assets,
                'account_behavior' => AccountBehavior::FLEXIBLE,
            ],
            [
                'code' => '103',
                'name' => 'QRIS',
                'category' => AccountCategory::Assets,
                'account_behavior' => AccountBehavior::TRANSIT_ONLY,
            ],
            [
                'code' => '104',
                'name' => 'Piutang',
                'category' => AccountCategory::Assets,
                'account_behavior' => AccountBehavior::TRANSIT_ONLY,
            ],

            // --- LIABILITIES ---
            [
                'code' => '201',
                'name' => 'Hutang Usaha',
                'category' => AccountCategory::Liabilities,
                'account_behavior' => AccountBehavior::CREDIT_ONLY,
            ],
            [
                'code' => '202',
                'name' => 'Kartu Kredit',
                'category' => AccountCategory::Liabilities,
                'account_behavior' => AccountBehavior::CREDIT_ONLY,
            ],

            // --- EQUITY ---
            [
                'code' => '301',
                'name' => 'Modal',
                'category' => AccountCategory::Equity,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
            [
                'code' => '302',
                'name' => 'Laba Ditahan',
                'category' => AccountCategory::Equity,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],

            // --- INCOME ---
            [
                'code' => '401',
                'name' => 'Iuran Anggota',
                'category' => AccountCategory::Income,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
            [
                'code' => '402',
                'name' => 'Donasi',
                'category' => AccountCategory::Income,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
            [
                'code' => '403',
                'name' => 'Pendapatan Acara',
                'category' => AccountCategory::Income,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],

            // --- EXPENSES ---
            [
                'code' => '501',
                'name' => 'Beban Gaji',
                'category' => AccountCategory::Expenses,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
            [
                'code' => '502',
                'name' => 'Beban Operasional',
                'category' => AccountCategory::Expenses,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
            [
                'code' => '503',
                'name' => 'Beban Acara',
                'category' => AccountCategory::Expenses,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
            [
                'code' => '504',
                'name' => 'Beban Konsumsi',
                'category' => AccountCategory::Expenses,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
            [
                'code' => '505',
                'name' => 'Beban Transport',
                'category' => AccountCategory::Expenses,
                'account_behavior' => AccountBehavior::NON_LIQUID,
            ],
        ];

        foreach ($accounts as $accountData) {
            Account::create($accountData);
        }

        $this->command->info('Default chart of accounts seeded successfully.');
    }
}
