<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthSeeder extends Seeder
{

    public function run(): void
    {
        // Create a test company (or retrieve if it already exists)
        $company = Company::firstOrCreate(
            ['name' => 'ABC Construction'],
            ['name' => 'ABC Construction'] // only 'name' is fillable; created_at will be set automatically
        );

        // Create an admin user for the company
        User::firstOrCreate(
            ['email' => 'admin@abcconstruction.com'],
            [
                'company_id' => $company->id,
                'name'       => 'Alice Admin',
                'email'      => 'admin@abcconstruction.com',
                'password'   => Hash::make('password'),
                'role'       => 'admin',
            ]
        );

        // Create a PM user for the company
        User::firstOrCreate(
            ['email' => 'pm@abcconstruction.com'],
            [
                'company_id' => $company->id,
                'name'       => 'Pete Manager',
                'email'      => 'pm@abcconstruction.com',
                'password'   => Hash::make('password'),
                'role'       => 'pm',
            ]
        );

        $this->command->info('Test company and users created successfully.');
        $this->command->info('Admin credentials: admin@abcconstruction.com / password');
        $this->command->info('PM credentials: pm@abcconstruction.com / password');
    }
}