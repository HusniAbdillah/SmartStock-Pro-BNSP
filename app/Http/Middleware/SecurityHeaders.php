<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $response->headers->set('Content-Security-Policy', $this->buildCsp());

        return $response;
    }

    private function buildCsp(): string
    {
        $scriptSrc = [
            "'self'",
            "'unsafe-inline'",
            "'unsafe-eval'",
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
        ];

        $styleSrc = [
            "'self'",
            "'unsafe-inline'",
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
        ];

        $connectSrc = [
            "'self'",
            'https://tile.openstreetmap.org',
            'https://*.tile.openstreetmap.org',
            'https://a.tile.openstreetmap.org',
            'https://b.tile.openstreetmap.org',
            'https://c.tile.openstreetmap.org',
        ];

        // Izinkan aset & HMR dari Vite dev server (npm run dev)
        foreach ($this->viteDevSources() as $source) {
            $scriptSrc[] = $source;
            $styleSrc[] = $source;
            $connectSrc[] = $source;
        }

        return implode('; ', [
            "default-src 'self'",
            'script-src ' . implode(' ', array_unique($scriptSrc)),
            'style-src ' . implode(' ', array_unique($styleSrc)),
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https: https://*.tile.openstreetmap.org https://tile.openstreetmap.org",
            'connect-src ' . implode(' ', array_unique($connectSrc)),
        ]);
    }

    /**
     * Origin Vite dev server — hanya di environment local.
     *
     * @return list<string>
     */
    private function viteDevSources(): array
    {
        if (! app()->environment('local')) {
            return [];
        }

        $sources = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://[::1]:5173',
            'http://localhost:5174',
            'http://127.0.0.1:5174',
            'ws://localhost:5173',
            'ws://127.0.0.1:5173',
            'ws://localhost:5174',
            'ws://127.0.0.1:5174',
        ];

        $hotPath = public_path('hot');

        if (is_readable($hotPath)) {
            $hotUrl = rtrim(trim((string) file_get_contents($hotPath)), '/');

            if ($hotUrl !== '') {
                $sources[] = $hotUrl;
                $sources[] = preg_replace('#^http#', 'ws', $hotUrl) ?: '';
            }
        }

        return array_values(array_filter(array_unique($sources)));
    }
}
