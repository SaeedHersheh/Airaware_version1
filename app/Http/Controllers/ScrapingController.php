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




//RESTFUL API FNUCTIONS!!!!!!!

private function getLatestWeatherData()
{
    $recentData = $this->firebase->getLatestCitiesData('weather_data',4);
    $latestByCity = [];
    $targetCityCount = 4;
    if($recentData){
        foreach($recentData as $entry){
            if(isset($entry['city_name'])){
                $latestByCity[$entry['city_name']] = $entry;
            }
        }
    }

    $actualCount = count($latestByCity);
    $efficiency  = ($targetCityCount>0) ? ($actualCount / $targetCityCount) * 100 : 0;

    return [ 
        'latestByCity' => array_values($latestByCity),
        'efficiency'   => $efficiency
    ];
}




public function showMapApi()
{
    $cleanedData = $this->getLatestWeatherData();

    return response()->json([
        'status' => 'success',
        'data' => $cleanedData
    ]);
}



}


// Think of your Controller as the "Construction Crew" and the Frontend JavaScript as the "Live News Feed."

// Even if you use the frontend to show live data, your Controller stays active because it performs three critical jobs that the frontend cannot do:

// 1. The Controller is the "Launchpad"
// When you first open the website, the JavaScript hasn't started yet. If you only used the frontend, the user would see a blank map and empty boxes for a few seconds while the browser connects to Firebase.

// The Controller fetches the most recent data from Firebase and injects it directly into the HTML before it even leaves the server.

// Result: The user sees a finished dashboard instantly. The Controller "primes the pump."

// 2. The Controller handles "Heavy Lifting" & Math
// In your ScrapingController.php, you have logic like:
// $efficiency = ($targetCityCount > 0) ? ($actualCount / $targetCityCount) * 100 : 0;

// The Controller does this calculation on the server.

// If you did this on the frontend, a user could technically "inspect element" and change the math. By keeping it in the Controller, your business logic stays hidden and "official."

// 3. The Controller acts as a "Security Shield"
// To use Firebase in the frontend, you need a configuration object (an API key/URL).

// If you let the frontend do everything (including writing data), anyone could find your keys and delete your database.

// Because your Controller (via the ScrapeWeather command) is the only one doing the "Writing" (saving new data), you can set your database to Read-Only for the public.

// Your Controller is the only one with the "Key" to change the data.

// How they work together (The Workflow):
// Server Side (Controller): * User requests the page.

// showMap() runs, gets the last 15 entries.

// Calculates the Efficiency.

// Sends the weather-map.blade.php to the browser with data already inside it.

// Client Side (Frontend JavaScript):

// The page appears instantly.

// JavaScript says: "Okay, I see the current data, now I'm going to listen to Firebase for any NEW updates that happen while the user is sitting here."

// When your background command (app:scrape-weather) pushes new data, the JavaScript catches it and updates the map without a refresh.

// Summary
// Your Controller is the Foundation (makes it work). The Frontend is the Polish (makes it live). You need both for a professional app.

// Would you like me to show you how to set up the Firebase "Read-Only" rules so your frontend is safe?














