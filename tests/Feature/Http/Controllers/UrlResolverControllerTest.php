<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UrlResolverControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    protected function get_decode(array $params = [])
    {
        return $this->json('GET', '/api/decode', $params);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->get_decode(['shortened_url' => 'https://example.com']);

        $response->assertStatus(401);
    }

    public function test_validation_fails_when_shortened_url_missing(): void
    {
        $this->authenticate();

        $response = $this->get_decode(['not_shortened_url' => 'wrong']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shortened_url']);
    }

    public function test_returns_cached_url_if_already_shortened(): void
    {
        $this->authenticate();

        $cached = 'https://example.com';
        $shortened = 'https://short.est/abc123';

        Redis::shouldReceive('get')
            ->once()
            ->with($shortened)
            ->andReturn($cached);

        $response = $this->get_decode(['shortened_url' => $shortened]);

        $response->assertStatus(200)
            ->assertJson([
                'shortened_url' => $shortened,
                'original_url' => $cached,
            ]);
    }

    public function test_returns_error_when_not_cached(): void
    {
        $this->authenticate();

        $shortened = 'https://short.est/not_found';

        Redis::shouldReceive('get')
            ->once()
            ->with($shortened)
            ->andReturn(null);

        $response = $this->get_decode(['shortened_url' => $shortened]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Short URL not found',
            ]);
    }
}
