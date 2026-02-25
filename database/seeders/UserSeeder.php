<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@seaguard.id'],
            [
                'name' => 'Admin SeaGuard',
                'password' => Hash::make('password123'),
            ]
        );
        
        $this->command->info('✅ User admin berhasil dipastikan ada!');
        $this->command->info('📧 Email: admin@seaguard.id');
        $this->command->info('🔑 Password: password123');
    }
}