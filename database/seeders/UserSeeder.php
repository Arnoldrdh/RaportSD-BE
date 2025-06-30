<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'kepala_sekolah',
            'email' => 'kepalasekolah@example.com',
            'password' => Hash::make('kepalasekolah123'),
            'role' => 'kepala_sekolah'
        ]);

        User::factory()->create([
            'name' => 'wali_kelas',
            'email' => 'walikelas@example.com',
            'password' => Hash::make('walikelas123'),
            'role' => 'wali_kelas'
        ]);

        User::factory()->create([
            'name' => 'murid1',
            'email' => 'murid1@example.com',
            'password' => Hash::make('murid123'),
            'role' => 'murid123'
        ]);
    }
}
