<?php

namespace Spatie\Newsletter;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\ServiceProvider;
use Spatie\Newsletter\Drivers\MailchimpDriver;

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
            if (is_null($driver) || $driver === 'log') {
                return new NullDriver($driver === 'log');
            }

            switch ($driver) {
                case 'mailchimp':
                    $driver = new MailchimpDriver(config('newsletter.mailchimp'));
            }

            return new Newsletter($driver);
        });

        $this->app->alias(Newsletter::class, 'newsletter');
    }
}
