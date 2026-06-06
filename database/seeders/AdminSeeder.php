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

        $hosts = [
            ['email' => 'dylan@storyboldly.org',  'name' => 'Dylan'],
            ['email' => 'cliff.flamer@gmail.com',  'name' => 'Cliff'],
            ['email' => 'mollyglock@gmail.com',    'name' => 'Molly'],
        ];

        foreach ($hosts as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('underground'),
                ]
            );
            $user->assignRole($role);
            $this->command->info("Host user ready: {$user->email}");
        }
    }
}
