<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Services\UrlShortenerService;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UrlShortenerControllerTest extends TestCase
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

    protected function post_encode(array $params = [])
    {
        return $this->json('POST', '/api/encode', $params);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->post_encode(['original_url' => 'https://example.com']);

        $response->assertStatus(401);
    }

    public function test_validation_fails_when_original_url_missing(): void
    {
        $this->authenticate();

        $response = $this->post_encode(['not_original_url' => 'wrong']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['original_url']);
    }

    public function test_returns_cached_url_if_already_shortened(): void
    {
        $this->authenticate();

        $original = 'https://example.com';
        $cached = 'https://short.est/abc123';

        Redis::shouldReceive('get')
            ->once()
            ->with($original)
            ->andReturn($cached);

        $response = $this->post_encode(['original_url' => $original]);

        $response->assertStatus(200)
            ->assertJson([
                'original_url' => $original,
                'shortened_url' => $cached,
            ]);
    }

    public function test_calls_service_to_generate_short_url_when_not_cached(): void
    {
        $this->authenticate();

        $original = 'https://example.com';
        $shortened = 'https://short.est/xyz789';

        Redis::shouldReceive('get')
            ->once()
            ->with($original)
            ->andReturn(null);
        Redis::shouldReceive('set')
            ->twice()
            ->andReturn(true);

        $this->mock(UrlShortenerService::class, function ($mock) use ($original, $shortened) {
            $mock->shouldReceive('generate')
                ->once()
                ->with($original)
                ->andReturn($shortened);
        });

        $response = $this->post_encode(['original_url' => $original]);

        $response->assertStatus(200)
            ->assertJson([
                'original_url' => $original,
                'shortened_url' => $shortened,
            ]);
    }
}
