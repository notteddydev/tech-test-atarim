<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class UrlShortenerService
{
    /**
     * Takes a URL and returns a unique "short" version.
     * 
     * @param string $original_url The "target" URL.
     * @return string The unique "short" version.
     */
    public function generate(string $original_url): string
    {
        $hash = hash('sha256', $original_url);
        $short_hash = substr($hash, 0, 6);
        $short_url = "https://short.est/{$short_hash}";
        
        // Ensure unique short URL.
        if (Redis::exists($short_url)) {
            return $this->generate($original_url . Str::random(5));
        }
        
        return $short_url;
    }
}