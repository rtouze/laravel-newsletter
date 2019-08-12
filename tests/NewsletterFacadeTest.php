<?php

namespace Spatie\Newsletter\Test;

use Spatie\Newsletter\Drivers\MailchimpDriver;
use Spatie\Newsletter\Drivers\NullDriver;
use Spatie\Newsletter\Newsletter;

class NewsletterFacadeTest extends TestCase
{
    /** @test */
    public function should_instantiate_facade_with_null_driver()
    {
        $newsletter = $this->app[Newsletter::class];
        $this->assertInstanceOf(NullDriver::class, $newsletter);
    }

    /** @test */
    public function should_instantiate_facade_with_mailchimp_driver()
    {
        config()->set('newsletter.driver', 'mailchimp');
        config()->set('newsletter.mailchimp.apiKey', 'mailchimp-keytest');

        $newsletter = $this->app[Newsletter::class];
        $this->assertInstanceOf(Newsletter::class, $newsletter);
        $this->assertInstanceOf(MailchimpDriver::class, $newsletter->driver);
    }

}