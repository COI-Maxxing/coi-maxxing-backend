<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Document;
use App\Models\Subcontractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subcontractor_id' => Subcontractor::factory(),
            'document_type' => fake()->randomElement(['coi', 'license', 'w9']),
            'status' => 'pending',
            'file_url' => fake()->url(),
            'uploaded_by' => fake()->safeEmail(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function pendingReview(): static
    {
        return $this->state(['status' => 'pending_review']);
    }

    public function valid(): static
    {
        return $this->state(['status' => 'valid']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }

    public function expired(): static
    {
        return $this->state(['status' => 'expired']);
    }

    public function coi(): static
    {
        return $this->state([
            'document_type' => 'coi',
            'insurer' => fake()->company(),
            'policy_number' => fake()->bothify('POL-####-??'),
            'coverage_amount' => fake()->randomFloat(2, 100000, 5000000),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'holder_name' => fake()->company(),
        ]);
    }
}
