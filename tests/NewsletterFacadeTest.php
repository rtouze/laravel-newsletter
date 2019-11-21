<?php

namespace DansMaCulotte\Newsletter\Test;

use DansMaCulotte\Newsletter\Drivers\MailchimpDriver;
use DansMaCulotte\Newsletter\Drivers\MailjetDriver;
use DansMaCulotte\Newsletter\Drivers\NullDriver;
use DansMaCulotte\Newsletter\Newsletter;

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

    /** @test */
    public function should_instantiate_facade_with_mailjet_driver()
    {
        config()->set('newsletter.driver', 'mailjet');
        config()->set('newsletter.mailjet.key', 'mailjet-keytest');
        config()->set('newsletter.mailjet.secret', 'mailjet-secrettest');

        $newsletter = $this->app[Newsletter::class];
        $this->assertInstanceOf(Newsletter::class, $newsletter);
        $this->assertInstanceOf(MailjetDriver::class, $newsletter->driver);
    }

}
