<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageImageController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        if (str_starts_with($path, 'payment-proofs/')) {
            abort(404);
        }

        return $this->imageResponse($path);
    }

    public function paymentProof(string $path): StreamedResponse
    {
        if (! str_starts_with($path, 'payment-proofs/')) {
            abort(404);
        }

        return $this->imageResponse($path);
    }

    private function imageResponse(string $path): StreamedResponse
    {
        $segments = explode('/', $path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (
            ! preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9._\/-]*\z/', $path)
            || in_array('..', $segments, true)
            || in_array('.', $segments, true)
            || ! in_array($extension, $allowedExtensions, true)
            || ! Storage::disk('public')->exists($path)
        ) {
            abort(404);
        }

        return Storage::disk('public')->response($path, basename($path), [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
