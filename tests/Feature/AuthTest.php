<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()
            ->for($this->company)
            ->admin()
            ->create(['password' => bcrypt('secret123')]);
    }

    // ── Login ─────────────────────────────────────────────

    public function test_login_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'email', 'name', 'role', 'company' => ['id', 'name']],
            ]);

        $this->assertEquals($this->user->id, $response->json('user.id'));
        $this->assertEquals($this->company->id, $response->json('user.company.id'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ── Me ─────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('user.id', $this->user->id)
            ->assertJsonPath('user.email', $this->user->email)
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonPath('user.company.id', $this->company->id);
    }

    public function test_me_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();
    }

    // ── Logout ─────────────────────────────────────────────

    public function test_logout_revokes_token(): void
    {
        // first login to get a real token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'secret123',
        ]);

        $token = $loginResponse->json('token');

        // logout with the token
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $logoutResponse->assertNoContent();

        // refresh the app to clear any cached token state
        $this->refreshApplication();

        // token should no longer work
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_logout_returns_401_when_unauthenticated(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();
    }
}
