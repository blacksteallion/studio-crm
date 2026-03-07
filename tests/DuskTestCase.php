<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
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
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): \Facebook\WebDriver\Remote\RemoteWebDriver
    {
        $options = (new \Facebook\WebDriver\Chrome\ChromeOptions)->addArguments(array_filter([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--headless=new',
            '--no-sandbox',             // CRITICAL: Required for WSL / Linux environments
            '--disable-gpu',            // CRITICAL: Required for WSL / Linux environments
            '--disable-dev-shm-usage',  // CRITICAL: Prevents memory crashes in headless mode
        ]));

        // Bypass Ubuntu's AppArmor sandbox by using our local testing binary
        $options->setBinary(base_path('chrome-linux64/chrome'));

        return \Facebook\WebDriver\Remote\RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            \Facebook\WebDriver\Remote\DesiredCapabilities::chrome()->setCapability(
                \Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $options
            )
        );
    }
}