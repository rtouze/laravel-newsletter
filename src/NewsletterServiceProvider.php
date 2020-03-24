<?php

namespace DansMaCulotte\Newsletter;

use DansMaCulotte\Newsletter\Drivers\MailjetDriver;
use Illuminate\Support\ServiceProvider;
use DansMaCulotte\Newsletter\Drivers\MailchimpDriver;
use DansMaCulotte\Newsletter\Drivers\NullDriver;

class NewsletterServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/newsletter.php', 'newsletter');

        $this->publishes([
            __DIR__.'/../config/newsletter.php' => config_path('newsletter.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton(Newsletter::class, function () {
            $driver = config('newsletter.driver', null);
            if ($driver === null || $driver === 'log') {
                return new NullDriver($driver === 'log');
            }

            $config = [
                'defaultList' => config('newsletter.defaultList'),
                'lists' => config('newsletter.lists'),
            ];

            switch ($driver) {
                case 'mailchimp':
                    $driver = new MailchimpDriver(config('newsletter.mailchimp'), $config);
                    break;
                case 'mailjet':
                    $driver = new MailjetDriver(config('newsletter.mailjet'), $config);
                    break;
            }

            return new Newsletter($driver);
        });

        $this->app->alias(Newsletter::class, 'newsletter');
    }
}
