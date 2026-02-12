<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Laravel HTTP client
use App\Http\Controllers\FirebaseController;

class ScrapingController extends Controller
{
    protected $firebase; 

    public function __construct() // must be public
    {
        $this->firebase = new FirebaseController();
    }

   public function scrapeWeather()
{
    
    $latitudes = "31.50,32.31,32.22,32.46";
    $longitudes = "34.47,35.03,35.26,35.30";
    $names = ['Gaza', 'Tulkarem', 'Nablus', 'Jenin'];

    $response = Http::get('https://api.open-meteo.com/v1/forecast', [
        'latitude' => $latitudes,
        'longitude' => $longitudes,
        'current_weather' => true
    ]);

    $dataArray = $response->json(); 

    foreach ($dataArray as $index => $cityWeather) {
        $cityWeather['city_name'] = $names[$index];
        $cityWeather['timestamp'] = now()->toDateTimeString();

        
        $this->firebase->saveData('weather_data', $cityWeather);
    }
}

// public function showMap()
// {
//     $data = $this->firebase->getData('weather_data'); // get all weather data from Firebase

//     return view('weather-map', ['weatherData' => $data]);
// }


// public function showMap()
// {
//     $data = $this->firebase->getNewestData('weather_data');
//     $latestWeather = !empty($data) ? reset($data) :null;
//     return view('weather-map', ['weatherData' => $latestWeather]);
// }


public function showMap()
{
    // 1. Get ONLY the latest 15 records (Super Fast)
    $recentData = $this->firebase->getLatestCitiesData('weather_data', 15);

    $latestByCity = [];
    $targetCityCount = 4;

    if ($recentData) {
        foreach ($recentData as $entry) {
            if (isset($entry['city_name'])) {
                // Overwrites older with newer, so we only keep the LATEST for each city
                $latestByCity[$entry['city_name']] = $entry;
            }
        }
    }

   // 2. Calculate Efficiency: (Actual / Target) * 100
    $actualCount = count($latestByCity);
    $efficiency = ($targetCityCount > 0) ? ($actualCount / $targetCityCount) * 100 : 0;

    // 3. Pass both to the view
    return view('weather-map', [
        'weatherData' => array_values($latestByCity),
        'efficiency'  => $efficiency
    ]);
}

}
