<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    public function obtenerClima(): array
    {
        return Cache::remember('clima_actual', now()->addMinutes(30), function () {
            $key  = config('services.openweather.key');
            $city = config('services.openweather.city');

            // 🔍 LOG 1: Ver qué está leyendo Laravel
            Log::info('OpenWeather DEBUG', [
                'key_vacia' => empty($key),
                'key_largo' => strlen($key ?? ''),
                'city'      => $city,
            ]);

            // Si la key está vacía, no perdemos tiempo con la petición
            if (empty($key)) {
                Log::error('OpenWeather: La API key está vacía. Revisa el .env y ejecuta php artisan config:clear');
                return $this->climaFallback($city);
            }

            try {
                $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q'     => $city,
                    'appid' => $key,
                    'units' => 'metric',
                    'lang'  => 'es',
                ]);

                // 🔍 LOG 2: Ver la respuesta completa
                Log::info('OpenWeather RESPONSE', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                if ($response->failed()) {
                    Log::error('OpenWeather FALLÓ: ' . $response->body());
                    return $this->climaFallback($city);
                }

                $data = $response->json();

                return [
                    'temp'   => round($data['main']['temp']) . '°C',
                    'desc'   => ucfirst($data['weather'][0]['description']),
                    'icono'  => $this->emojiDesdeIcono($data['weather'][0]['icon']),
                    'ciudad' => $data['name'] . ', ' . ($data['sys']['country'] ?? ''),
                ];
            } catch (\Exception $e) {
                // 🔍 LOG 3: Ver excepciones (timeout, DNS, etc.)
                Log::error('OpenWeather EXCEPCIÓN: ' . $e->getMessage());
                return $this->climaFallback($city);
            }
        });
    }

    private function climaFallback(string $city): array
    {
        return [
            'temp'   => '--',
            'desc'   => 'No disponible',
            'icono'  => '🌤️',
            'ciudad' => $city,
        ];
    }

    private function emojiDesdeIcono(string $icon): string
    {
        $codigo  = substr($icon, 0, 2);
        $esNoche = str_ends_with($icon, 'n');

        return match ($codigo) {
            '01' => $esNoche ? '🌙' : '☀️',
            '02' => $esNoche ? '🌙' : '🌤️',
            '03' => '⛅',
            '04' => '☁️',
            '09' => '🌧️',
            '10' => '🌦️',
            '11' => '⛈️',
            '13' => '❄️',
            '50' => '🌫️',
            default => '🌤️',
        };
    }
}