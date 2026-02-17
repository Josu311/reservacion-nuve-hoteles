<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SocialShareMeta
{
    public function handle(Request $request, Closure $next)
    {
        // Solo aplica a páginas (GET/HEAD) y no a peticiones XHR/JSON
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }
        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return $next($request);
        }

        $ua = strtolower($request->userAgent() ?? '');

        // Bots típicos de redes (NO incluyas googlebot)
        $isSocialBot = preg_match('/facebookexternalhit|facebot|twitterbot|linkedinbot|whatsapp|telegrambot|discordbot|slackbot|skypeuripreview/i', $ua);

        if (! $isSocialBot) {
            return $next($request);
        }

        // Mapeo por path (ajústalo a tus URLs reales)
        $path = '/'.trim($request->path(), '/');
        if ($path === '/') $path = '/'; // cuando path() regresa ""

        $meta = $this->metaForPath($path);

        // Si no hay meta definida para esa ruta, sigue normal
        if ($meta === null) {
            return $next($request);
        }

        // Asegura URLs absolutas
        $meta['url'] = $meta['url'] ?? $request->fullUrl();
        $meta['canonical'] = $meta['canonical'] ?? $request->url();

        return response()
            ->view('share', $meta)
            // puedes cachear un poquito para reducir carga
            ->header('Cache-Control', 'public, max-age=300');
    }

    private function metaForPath(string $path): ?array
    {
        $base = 'https://nuveexpress.com.mx';

        $map = [
            '/' => [
                'title' => 'Nuve Express | Hoteles en Torreón, Coahuila',
                'description' => 'Hoteles con ubicación estratégica en Torreón. Hospedaje cómodo y accesible. Reserva en línea en minutos.',
                'image' => $base.'/img/nuve-express-og-image.png',
                'canonical' => $base.'/',
                'url' => $base.'/',
            ],
            '/nosotros' => [
                'title' => 'Nosotros | Nuve Express – Hoteles en Torreón',
                'description' => 'Conoce Nuve Express: hoteles con ubicación estratégica en Torreón, Coahuila. Reserva en línea en minutos.',
                'image' => $base.'/img/nuve-express-og-image.png',
                'canonical' => $base.'/nosotros',
                'url' => $base.'/nosotros',
            ],
            '/hoteles' => [
                'title' => 'Ubicaciones Nuve Express en Torreón | Abasolo y Corregidora',
                'description' => 'Encuentra nuestras ubicaciones en Torreón, Coahuila. Revisa el mapa de Abasolo y Corregidora y reserva tu estancia en línea.',
                'image' => $base.'/img/nuve-express-og-image.png',
                'canonical' => $base.'/hoteles',
                'url' => $base.'/hoteles',
            ],
            '/disponibilidad' => [
                'title' => 'Disponibilidad | Nuve Express',
                'description' => 'Busca habitaciones por fechas, número de habitaciones y adultos. Reserva tu estancia en Nuve Express.',
                'image' => $base.'/img/nuve-express-og-image.png',
                'canonical' => $base.'/disponibilidad',
                'url' => $base.'/disponibilidad',
            ],
        ];

        return $map[$path] ?? null;
    }
}
