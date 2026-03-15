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
            // Use Laravel's native driver
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        // Ensure the profile directory exists so Chromium doesn't crash on boot
        $profilePath = storage_path('dusk-profile');
        if (!file_exists($profilePath)) {
            mkdir($profilePath, 0777, true);
        }

        $options = (new ChromeOptions)->addArguments([
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--no-sandbox',               
            '--disable-dev-shm-usage',    
            '--disable-gpu',              
            '--headless',             
            // THE FIX: Bridge the Snap gap by forcing the connection into the storage folder
            '--user-data-dir=' . $profilePath,
            '--crash-dumps-dir=' . $profilePath,
        ]);

        $chromeBinary = '/snap/bin/chromium';
        
        if (file_exists($chromeBinary)) {
            $options->setBinary($chromeBinary);
        }

        return RemoteWebDriver::create(
            // Use IPv4 to prevent connection rejections
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://127.0.0.1:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}