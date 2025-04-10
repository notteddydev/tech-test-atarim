<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecodeUrlRequest;
use Illuminate\Support\Facades\Redis;

class UrlResolverController extends Controller
{
    /**
     * Takes a "short" URL and returns the original, or the "target" URL in a JSON response.
     * 
     * @param EncodeUrlRequest $request The request object.
     * @return JsonResponse A JSON array containing either the "target" URL, or an error message.
     */
    public function __invoke(DecodeUrlRequest $request)
    {
        $original_url = Redis::get($request->shortened_url);

        if (is_null($original_url)) {
            return response()->json([
                'error' => 'Short URL not found',
            ], 404);
        }

        return response()->json([
            'original_url' => $original_url,
            'shortened_url' => $request->shortened_url,
        ]);
    }
}
