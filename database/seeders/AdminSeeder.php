<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'host', 'guard_name' => 'web']);

        $user = User::firstOrCreate(
            ['email' => 'dylan@storyboldly.org'],
            [
                'name'     => 'Dylan',
                'password' => Hash::make('underground'),
            ]
        );

        $user->assignRole($role);

        $this->command->info("Host user ready: {$user->email}");
    }
}
