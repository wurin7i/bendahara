<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\DivisionAccount;
use Illuminate\Database\Seeder;
use WuriN7i\Balance\Models\Account;

class DivisionAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get common liquid accounts (saku)
        $kas = Account::where('code', '101')->first();
        $bank = Account::where('code', '102')->first();
        $qris = Account::where('code', '103')->first();

        // Get all divisions
        $divisions = Division::all();

        $mappingsCount = 0;

        foreach ($divisions as $division) {
            // Each division gets Kas, Bank, and QRIS accounts by default
            $mappings = [
                [
                    'account' => $kas,
                    'alias_name' => "Kas {$division->name}",
                ],
                [
                    'account' => $bank,
                    'alias_name' => "Bank {$division->name}",
                ],
                [
                    'account' => $qris,
                    'alias_name' => "QRIS {$division->name}",
                ],
            ];

            foreach ($mappings as $mapping) {
                if ($mapping['account']) {
                    DivisionAccount::create([
                        'division_id' => $division->id,
                        'account_id' => $mapping['account']->id,
                        'alias_name' => $mapping['alias_name'],
                        'is_active' => true,
                    ]);
                    $mappingsCount++;
                }
            }
        }

        $this->command->info("✓ Created {$mappingsCount} division account mappings");
    }
}
