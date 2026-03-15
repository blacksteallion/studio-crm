<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            // NEW: Force Dusk to use the perfectly matched Snap ChromeDriver
            if (file_exists('/snap/bin/chromium.chromedriver')) {
                static::useChromedriver('/snap/bin/chromium.chromedriver');
            }
            
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--no-sandbox',               
            '--disable-dev-shm-usage',    
            '--disable-gpu',              
            '--headless=new',             
            '--remote-debugging-port=9222', // NEW: Helps prevent background hanging
        ]);

        $chromeBinary = env('CHROME_EXECUTABLE', '/snap/bin/chromium');
        
        if (file_exists($chromeBinary)) {
            $options->setBinary($chromeBinary);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://127.0.0.1:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}