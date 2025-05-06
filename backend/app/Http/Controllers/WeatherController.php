<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function getWeather(Request $request)
    {
        $city = $request->query('city');

        if (!$city) {
            return response()->json(['error' => 'City parameter is required.'], 400);
        }

        $geoResponse = Http::withHeaders([
            'User-Agent' => 'YourAppName/1.0'
        ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $city,
                    'format' => 'json',
                    'limit' => 1,
                ]);

        if ($geoResponse->failed() || count($geoResponse->json()) === 0) {
            return response()->json(['error' => 'City not found.'], 404);
        }

        $location = $geoResponse->json()[0];
        $lat = $location['lat'];
        $lon = $location['lon'];

        $weatherResponse = Http::get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $lat,
            'longitude' => $lon,
            'current_weather' => true,
        ]);

        if ($weatherResponse->failed()) {
            return response()->json(['error' => 'Weather API error.'], 500);
        }

        $weather = $weatherResponse->json()['current_weather'];

        return response()->json([
            'city' => $city,
            'temperature' => $weather['temperature'],
            'weather_description' => 'Code: ' . $weather['weathercode'],
            'icon' => null,
        ]);
    }
}
