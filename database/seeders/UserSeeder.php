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
        ]);

        $employees = [
            ['npk' => '5584', 'name' => 'Jonny Ferry N.'],
            ['npk' => '6740', 'name' => 'Ervan Aji'],
            ['npk' => '10703', 'name' => 'Dwie Muriati'],
            ['npk' => '17177', 'name' => 'Sigit Purnomo'],
            ['npk' => '33824', 'name' => 'Imam Husni'],
            ['npk' => '35318', 'name' => 'Didik Setyawan'],
            ['npk' => '35735', 'name' => 'Hendri'],
            ['npk' => '35741', 'name' => 'Amirullah'],
            ['npk' => '35797', 'name' => 'Ricky Dwijayanto'],
            ['npk' => '41463', 'name' => 'Muhammad Ramadhan Makaado'],
            ['npk' => '45397', 'name' => 'Tian Permata Sari'],
            ['npk' => '50158', 'name' => 'Ridwan Supratman'],
            ['npk' => '57486', 'name' => 'Riajeng Ratri Amalia Indra Budiman'],
            ['npk' => '58375', 'name' => 'Haikal Fihkri Mukhlisin'],
            ['npk' => '58515', 'name' => 'Agit Agustian Mahendra'],
            ['npk' => '59280', 'name' => 'Hardian Indra Rukmana'],
            ['npk' => '59281', 'name' => 'Syabila Nurhamni'],
        ];

        $superusers = [
            ['npk' => '53678', 'name' => 'Radinal Wibisono'],
            ['npk' => '55786', 'name' => 'Citra Ashilla Zahrantiara'],
            ['npk' => '58065', 'name' => 'Karlina Ibrahim'],
            ['npk' => '58484', 'name' => 'Muhammad Imam Dinnul Haq'],
            ['npk' => '58485', 'name' => 'Cindy Elviana Tan'],
        ];

        foreach ($employees as $data) {
            User::factory()->employee()->create([
                'name' => $data['name'],
                'npk' => $data['npk'],
                'email' => $data['npk'].'@tripmonitor.local',
            ]);
        }

        foreach ($superusers as $data) {
            User::factory()->superuser()->create([
                'name' => $data['name'],
                'npk' => $data['npk'],
                'email' => $data['npk'].'@tripmonitor.local',
            ]);
        }
    }
}
