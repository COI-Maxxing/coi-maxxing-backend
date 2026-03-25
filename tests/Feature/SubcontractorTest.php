<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Document;
use App\Models\Subcontractor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcontractorTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private User $pm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->admin = User::factory()->for($this->company)->admin()->create();
        $this->pm = User::factory()->for($this->company)->pm()->create();
    }

    // ── Index ──────────────────────────────────────────────

    public function test_index_returns_subcontractors_with_documents(): void
    {
        $sub = Subcontractor::factory()->for($this->company)->create();
        Document::factory()->for($sub)->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/subcontractors');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'business_name', 'documents']]]);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/api/subcontractors')->assertUnauthorized();
    }

    public function test_index_only_returns_own_company_subcontractors(): void
    {
        // our company's sub
        Subcontractor::factory()->for($this->company)->create();

        // another company's sub
        $otherCompany = Company::factory()->create();
        Subcontractor::factory()->for($otherCompany)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/subcontractors');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    // ── Store ──────────────────────────────────────────────

    public function test_store_creates_subcontractor(): void
    {
        $payload = [
            'business_name' => 'Acme Electric LLC',
            'contact_name' => 'Jane Doe',
            'contact_email' => 'jane.doe@gmail.com',
            'contact_phone' => '555-0100',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/subcontractors', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.business_name', 'Acme Electric LLC')
            ->assertJsonPath('data.company_id', $this->company->id);

        $this->assertDatabaseHas('subcontractors', [
            'business_name' => 'Acme Electric LLC',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_store_requires_business_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/subcontractors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['business_name']);
    }

    public function test_store_allowed_for_pm_role(): void
    {
        $response = $this->actingAs($this->pm)
            ->postJson('/api/subcontractors', [
                'business_name' => 'PM Created Sub',
            ]);

        $response->assertCreated();
    }

    // ── Show ──────────────────────────────────────────────

    public function test_show_returns_subcontractor_with_documents_and_events(): void
    {
        $sub = Subcontractor::factory()->for($this->company)->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/subcontractors/{$sub->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'business_name', 'documents']]);
    }

    public function test_show_returns_404_for_nonexistent_subcontractor(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/subcontractors/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_other_company_subcontractor(): void
    {
        $otherCompany = Company::factory()->create();
        $otherSub = Subcontractor::factory()->for($otherCompany)->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/subcontractors/{$otherSub->id}");

        // BelongsToCompany scope prevents cross-company access → 404
        $response->assertNotFound();
    }

    // ── Destroy ───────────────────────────────────────────

    public function test_destroy_deletes_subcontractor(): void
    {
        $sub = Subcontractor::factory()->for($this->company)->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/subcontractors/{$sub->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('subcontractors', ['id' => $sub->id]);
    }

    public function test_destroy_returns_404_for_other_company(): void
    {
        $otherCompany = Company::factory()->create();
        $otherSub = Subcontractor::factory()->for($otherCompany)->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/subcontractors/{$otherSub->id}");

        $response->assertNotFound();
    }
}
