<?php

namespace App\Support;

use Illuminate\Http\Request;

class PublicImageUrl
{
    public static function resolve(Request $request, ?string $imageUrl): ?string
    {
        if ($imageUrl === null || trim($imageUrl) === '') {
            return null;
        }

        $imageUrl = trim($imageUrl);
        $imageHost = parse_url($imageUrl, PHP_URL_HOST);
        if ($imageHost !== null && $imageHost !== $request->getHost()) {
            return $imageUrl;
        }

        $path = parse_url($imageUrl, PHP_URL_PATH) ?: $imageUrl;
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/destinations/')
            || str_starts_with($path, 'storage/articles/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (! preg_match('#\A(?:destinations|articles)/[A-Za-z0-9_./-]+\.(?:jpe?g|png|webp|gif)\z#i', $path)
            || str_contains($path, '..')) {
            return $imageUrl;
        }

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));

        return $request->getSchemeAndHttpHost().'/api/v1/media/'.$encodedPath;
    }
}
