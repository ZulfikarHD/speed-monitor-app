<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * User Seeder
 *
 * Seeds users based on real employee/superuser data.
 * All users can login with their NPK and default password 'password'.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin account
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'npk' => '00001',
            'divisi' => 'IT',
            'departement' => 'Information Technology',
            'section' => 'System Administration',
        ]);

        $employees = [
            [
                'npk' => '5584',
                'name' => 'Jonny Ferry N.',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '6740',
                'name' => 'Ervan Aji',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '10703',
                'name' => 'Dwie Muriati',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Koordinator Driver',
            ],
            [
                'npk' => '17177',
                'name' => 'Sigit Purnomo',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '33824',
                'name' => 'Imam Husni',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Khusus',
            ],
            [
                'npk' => '35318',
                'name' => 'Didik Setyawan',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '35735',
                'name' => 'Hendri',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '35741',
                'name' => 'Amirullah',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '35797',
                'name' => 'Ricky Dwijayanto',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '41463',
                'name' => 'Muhammad Ramadhan Makaado',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Ekspedisi',
            ],
            [
                'npk' => '45397',
                'name' => 'Tian Permata Sari',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Admin Transport',
            ],
            [
                'npk' => '50158',
                'name' => 'Ridwan Supratman',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '57486',
                'name' => 'Riajeng Ratri Amalia Indra Budiman',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Admin Transport',
            ],
            [
                'npk' => '58375',
                'name' => 'Haikal Fihkri Mukhlisin',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '58515',
                'name' => 'Agit Agustian Mahendra',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '59280',
                'name' => 'Hardian Indra Rukmana',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Driver Pool',
            ],
            [
                'npk' => '59281',
                'name' => 'Syabila Nurhamni',
                'divisi' => 'Operasional',
                'departement' => 'Transport & Logistik',
                'section' => 'Admin Transport',
            ],
        ];

        $superusers = [
            [
                'npk' => '53678',
                'name' => 'Radinal Wibisono',
                'divisi' => 'Manajemen',
                'departement' => 'Operations Management',
                'section' => 'Supervisor Operasional',
            ],
            [
                'npk' => '55786',
                'name' => 'Citra Ashilla Zahrantiara',
                'divisi' => 'Manajemen',
                'departement' => 'Operations Management',
                'section' => 'Supervisor Logistik',
            ],
            [
                'npk' => '58065',
                'name' => 'Karlina Ibrahim',
                'divisi' => 'Manajemen',
                'departement' => 'Safety & Compliance',
                'section' => 'Safety Officer',
            ],
            [
                'npk' => '58484',
                'name' => 'Muhammad Imam Dinnul Haq',
                'divisi' => 'Manajemen',
                'departement' => 'Operations Management',
                'section' => 'Koordinator Armada',
            ],
            [
                'npk' => '58485',
                'name' => 'Cindy Elviana Tan',
                'divisi' => 'Manajemen',
                'departement' => 'Safety & Compliance',
                'section' => 'Compliance Officer',
            ],
        ];

        foreach ($employees as $data) {
            User::factory()->employee()->create([
                'name' => $data['name'],
                'npk' => $data['npk'],
                'email' => $data['npk'].'@tripmonitor.local',
                'divisi' => $data['divisi'],
                'departement' => $data['departement'],
                'section' => $data['section'],
            ]);
        }

        foreach ($superusers as $data) {
            User::factory()->superuser()->create([
                'name' => $data['name'],
                'npk' => $data['npk'],
                'email' => $data['npk'].'@tripmonitor.local',
                'divisi' => $data['divisi'],
                'departement' => $data['departement'],
                'section' => $data['section'],
            ]);
        }
    }
}
