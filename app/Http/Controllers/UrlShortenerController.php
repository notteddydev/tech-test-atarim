<?php

namespace App\Http\Controllers;

use App\Http\Requests\EncodeUrlRequest;
use App\Services\UrlShortenerService;
use Illuminate\Support\Facades\Redis;

class UrlShortenerController extends Controller
{
    protected UrlShortenerService $url_shortener_service;

    public function __construct(UrlShortenerService $url_shortener_service)
    {
        $this->url_shortener_service = $url_shortener_service;
    }

    /**
     * Takes a URL and returns a "short" version in a JSON response.
     * 
     * @param EncodeUrlRequest $request The request object.
     * @return JsonResponse A JSON array containing the "short" URL.
     */
    public function __invoke(EncodeUrlRequest $request)
    {
        $shortened_url = Redis::get($request->original_url);

        if (is_null($shortened_url)) {
            $shortened_url = $this->url_shortener_service->generate($request->original_url);

            // Allows for quick lookup either way round.
            Redis::set($shortened_url, $request->original_url);
            Redis::set($request->original_url, $shortened_url);
        }

        return response()->json([
            'original_url' => $request->original_url,
            'shortened_url' => $shortened_url,
        ]);
    }
}
