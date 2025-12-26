<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Pusat',
                'code' => 'PUSAT',
                'description' => 'Divisi Pusat - Koordinasi dan administrasi umum organisasi',
                'is_active' => true,
            ],
            [
                'name' => 'Acara',
                'code' => 'ACARA',
                'description' => 'Divisi Acara - Perencanaan dan pelaksanaan kegiatan organisasi',
                'is_active' => true,
            ],
            [
                'name' => 'Humas',
                'code' => 'HUMAS',
                'description' => 'Divisi Hubungan Masyarakat - Komunikasi dan publikasi',
                'is_active' => true,
            ],
            [
                'name' => 'Keuangan',
                'code' => 'KEUANGAN',
                'description' => 'Divisi Keuangan - Pengelolaan keuangan organisasi',
                'is_active' => true,
            ],
            [
                'name' => 'Olahraga',
                'code' => 'OLAHRAGA',
                'description' => 'Divisi Olahraga - Kegiatan dan kompetisi olahraga',
                'is_active' => true,
            ],
            [
                'name' => 'Seni Budaya',
                'code' => 'SENBUD',
                'description' => 'Divisi Seni dan Budaya - Kegiatan seni dan pelestarian budaya',
                'is_active' => true,
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }

        $this->command->info('✓ Created ' . count($divisions) . ' divisions');
    }
}
