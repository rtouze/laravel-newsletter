<?php

namespace DansMaCulotte\Newsletter\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use DansMaCulotte\Newsletter\NewsletterServiceProvider;

class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            NewsletterServiceProvider::class,
        ];
    }
    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config()->set('newsletter.driver', 'log');
    }
}
