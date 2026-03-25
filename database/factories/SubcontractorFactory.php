<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Subcontractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subcontractor>
 */
class SubcontractorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'business_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
        ];
    }
}
