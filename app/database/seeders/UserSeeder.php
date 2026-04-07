<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'tenant_id' => 1, // Vinculado ao tenant criado no passo anterior
            'name' => 'Luciano',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345'), // Mude para a senha que desejar testar
            'role' => 'admin',
        ]);
    }
}