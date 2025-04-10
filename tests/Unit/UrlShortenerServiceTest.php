<?php

namespace Tests\Unit;

use App\Services\UrlShortenerService;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class UrlShortenerServiceTest extends TestCase
{
    public function test_generate_creates_expected_short_url(): void
    {
        $original = 'https://example.com';
        $expected = 'https://short.est/' . substr(hash('sha256', $original), 0, 6);

        Redis::shouldReceive('exists')
            ->once()
            ->with($expected)
            ->andReturn(false);

        $service = new UrlShortenerService();
        $result = $service->generate($original);

        $this->assertEquals($expected, $result);
    }

    public function test_generate_retries_on_collision(): void
    {
        $original = 'https://example.com';
        $firstAttempt = 'https://short.est/' . substr(hash('sha256', $original), 0, 6);

        Redis::shouldReceive('exists')
            ->once()
            ->with($firstAttempt)
            ->andReturn(true);

        Redis::shouldReceive('exists')
            ->once()
            ->withArgs(function ($key) use ($firstAttempt) {
                return str_starts_with($key, 'https://short.est/') && $key !== $firstAttempt;
            })
            ->andReturn(false);

        $service = new UrlShortenerService();
        $result = $service->generate($original);

        $this->assertStringStartsWith('https://short.est/', $result);
        $this->assertNotEquals($firstAttempt, $result);
    }
}