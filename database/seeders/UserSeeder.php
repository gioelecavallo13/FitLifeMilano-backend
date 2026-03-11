<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Creazione AMMINISTRATORE
        User::create([
            'first_name' => 'Mario',
            'last_name'  => 'Admin',
            'email'      => 'admin@fitlife.it',
            'password'   => Hash::make('password123'),
            'role'       => 'admin',
        ]);

        // Creazione COACH
        User::create([
            'first_name' => 'Marco',
            'last_name'  => 'Trainer',
            'email'      => 'coach@fitlife.it',
            'password'   => Hash::make('password123'),
            'role'       => 'coach',
        ]);

        // Creazione CLIENTE
        User::create([
            'first_name' => 'Luca',
            'last_name'  => 'Cliente',
            'email'      => 'cliente@fitlife.it',
            'password'   => Hash::make('password123'),
            'role'       => 'client',
        ]);
    }
}