<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Document;
use App\Models\Subcontractor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private User $pm;
    private Subcontractor $sub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->admin = User::factory()->for($this->company)->admin()->create();
        $this->pm = User::factory()->for($this->company)->pm()->create();
        $this->sub = Subcontractor::factory()->for($this->company)->create();
    }

    // ── Index (via subcontractor) ─────────────────────────

    public function test_index_returns_documents_for_subcontractor(): void
    {
        Document::factory()->count(2)->for($this->sub)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/subcontractors/{$this->sub->id}/documents");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_returns_404_for_other_company_subcontractor(): void
    {
        $otherCompany = Company::factory()->create();
        $otherSub = Subcontractor::factory()->for($otherCompany)->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/subcontractors/{$otherSub->id}/documents");

        $response->assertNotFound();
    }

    // ── Store ──────────────────────────────────────────────

    public function test_store_creates_document_with_pending_status(): void
    {
        $payload = [
            'subcontractor_id' => $this->sub->id,
            'document_type' => 'coi',
            'file_url' => 'https://storage.example.com/docs/coi-001.pdf',
            'uploaded_by' => 'uploader@example.com',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/documents', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.document_type', 'coi')
            ->assertJsonPath('data.company_id', $this->company->id);

        $this->assertDatabaseHas('documents', [
            'subcontractor_id' => $this->sub->id,
            'status' => 'pending',
        ]);
    }

    public function test_store_creates_uploaded_audit_event(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/documents', [
                'subcontractor_id' => $this->sub->id,
                'document_type' => 'w9',
            ]);

        $documentId = $response->json('data.id');

        $this->assertDatabaseHas('document_events', [
            'document_id' => $documentId,
            'event_type' => 'uploaded',
            'actor' => $this->admin->email,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/documents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subcontractor_id', 'document_type']);
    }

    public function test_store_validates_document_type_enum(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/documents', [
                'subcontractor_id' => $this->sub->id,
                'document_type' => 'invalid_type',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document_type']);
    }

    public function test_store_returns_401_when_unauthenticated(): void
    {
        $this->postJson('/api/documents', [])->assertUnauthorized();
    }

    // ── Show ──────────────────────────────────────────────

    public function test_show_returns_document_with_events_and_subcontractor(): void
    {
        $doc = Document::factory()->for($this->sub)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/documents/{$doc->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'status', 'document_type', 'events', 'subcontractor'],
            ]);
    }

    public function test_show_returns_404_for_other_company_document(): void
    {
        $otherCompany = Company::factory()->create();
        $otherSub = Subcontractor::factory()->for($otherCompany)->create();
        $otherDoc = Document::factory()->for($otherSub)->create([
            'company_id' => $otherCompany->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/documents/{$otherDoc->id}");

        $response->assertNotFound();
    }

    // ── Confirm (validate) ────────────────────────────────

    public function test_confirm_transitions_pending_review_to_valid(): void
    {
        $doc = Document::factory()->for($this->sub)->pendingReview()->create([
            'company_id' => $this->company->id,
        ]);

        $corrections = [
            'insurer' => 'Corrected Insurer Inc.',
            'policy_number' => 'POL-CORRECTED-001',
        ];

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/documents/{$doc->id}/validate", $corrections);

        $response->assertOk()
            ->assertJsonPath('data.status', 'valid')
            ->assertJsonPath('data.insurer', 'Corrected Insurer Inc.');

        $this->assertDatabaseHas('document_events', [
            'document_id' => $doc->id,
            'event_type' => 'validated',
        ]);
    }

    public function test_confirm_returns_409_when_document_not_pending_review(): void
    {
        $doc = Document::factory()->for($this->sub)->pending()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/documents/{$doc->id}/validate", []);

        $response->assertStatus(409)
            ->assertJsonPath('current_status', 'pending');
    }

    public function test_confirm_allowed_for_pm_role(): void
    {
        $doc = Document::factory()->for($this->sub)->pendingReview()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->pm)
            ->patchJson("/api/documents/{$doc->id}/validate", []);

        $response->assertOk();
    }

    // ── Reject ────────────────────────────────────────────

    public function test_reject_transitions_pending_review_to_rejected(): void
    {
        $doc = Document::factory()->for($this->sub)->pendingReview()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/documents/{$doc->id}/reject", [
                'reason' => 'Coverage amount too low',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('document_events', [
            'document_id' => $doc->id,
            'event_type' => 'rejected',
        ]);
    }

    public function test_reject_returns_409_when_document_not_pending_review(): void
    {
        $doc = Document::factory()->for($this->sub)->valid()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/documents/{$doc->id}/reject", []);

        $response->assertStatus(409)
            ->assertJsonPath('current_status', 'valid');
    }

    public function test_reject_allowed_for_pm_role(): void
    {
        $doc = Document::factory()->for($this->sub)->pendingReview()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->pm)
            ->patchJson("/api/documents/{$doc->id}/reject", [
                'reason' => 'PM rejected this document',
            ]);

        $response->assertOk();
    }
}
