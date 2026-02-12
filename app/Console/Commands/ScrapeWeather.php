<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ScrapingController;

class ScrapeWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-weather';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scraper = new ScrapingController();
        $result = $scraper->scrapeWeather();

        $this->info('weather data scraped at ' . now());
    }
}
//It "knows" through a process called Service Registration. Laravel basically scans your files to create a map of names (signatures) and the classes they belong to.

// Here is the exact chain of how that name is connected to your code:

// 1. The Signature (The Connection Point)
// Inside your ScrapeWeather.php file, you defined a property called $signature.

// PHP
// protected $signature = 'app:scrape-weather';
// When Laravel boots up, it looks through the app/Console/Commands folder. It reads every file and says: "Okay, I found a class named ScrapeWeather, and its ID is app:scrape-weather." It stores this in a list in the background.

// 2. The Command Lookup
// When you write Schedule::command('app:scrape-weather') in your console routes, Laravel doesn't look for a file name; it looks for that ID in its internal list.

// The Scheduler says: "I need to run app:scrape-weather."

// The Kernel checks its list: "app:scrape-weather? Oh, that belongs to the class App\Console\Commands\ScrapeWeather."

// The Action: Laravel creates a new instance of that class and hits the "Start" button (the handle() method).

// 3. How the Handle knows what to do
// Once the handle() method is triggered, it follows your instructions:

// It creates the Brain (new ScrapingController()).

// It tells that brain to run the Scrape function (scrapeWeather()).

// The brain then makes the API Call and uses the Bridge (FirebaseController) to save it.

// Summary: What is the ID connected to?
// The ID name (app:scrape-weather) is connected to The Class (ScrapeWeather).

// The Class is connected to The Method (handle()).

// The Method is connected to The Controller (ScrapingController).



// This handle() function is the actual "To-Do List" of your command. When Laravel triggers your command (either by you typing it in the terminal or the scheduler running it), this is the specific code that wakes up and does the work.

// Here is the line-by-line breakdown:

// 1. $scraper = new ScrapingController();
// This line creates an instance of your ScrapingController. It’s basically hiring the "Weather Expert" you already built. Since the controller has a __construct that sets up Firebase, this one line also prepares the connection to Google.

// 2. $result = $scraper->scrapeWeather();
// This is where the magic happens. It tells the controller to:

// Call the Internet: Reach out to the Open-Meteo API.

// Get the Data: Grab the temperature and wind speed for Palestine.

// Push to Firebase: Send that data straight to your Realtime Database.

// All of that logic is tucked away inside scrapeWeather(), and this line just says "Go!"

// 3. $this->info('weather data scraped at ' . now());
// This is for you (the developer).

// Since the scheduler usually runs in the background, you wouldn't know if it worked just by looking at the screen.

// This line prints a nice green message in your terminal window like: weather data scraped at 2024-05-20 10:05:01.

// It uses the now() helper to show exactly when the database was updated.

// Think of it like a "Button"
// Imagine you have a big physical button on your desk labeled "SCRAPE".

// The Signature (app:scrape-weather) is the Label on the button.

// The Handle (public function handle()) is the Wiring behind the button.

// When you press the button (run the command), the wiring triggers the ScrapingController, the controller talks to the API and Firebase, and then the little screen next to the button says "Success!"

// Why did you put it here instead of just in the Controller? Because Controllers are meant for browsers. By putting this logic in a Command's handle(), you allow Laravel to run the code without a browser—which is exactly how the Scheduler works!