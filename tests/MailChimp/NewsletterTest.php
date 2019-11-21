<?php

namespace DansMaCulotte\Newsletter\Test;

use Mockery;
use DrewM\MailChimp\MailChimp;
use PHPUnit\Framework\TestCase;
use DansMaCulotte\Newsletter\Drivers\MailchimpDriver;
use DansMaCulotte\Newsletter\Exceptions\ApiError;
use DansMaCulotte\Newsletter\Newsletter;

class NewsletterTest extends TestCase
{
    protected $driver;

    /** @var Mockery\Mock */
    protected $client;

    /** @var \DansMaCulotte\Newsletter\Newsletter */
    protected $newsletter;

    public function setUp() : void
    {
        $this->client = Mockery::mock(MailChimp::class);

        $this->driver = new MailchimpDriver([
            'apiKey' => 'test-ApiKey',
            'ssl' => false,
        ], [
            'lists' => [
                'list1' => ['id' => 123],
                'list2' => ['id' => 456],
            ],
            'defaultList' => 'list1',
        ]);

        $this->driver->client = $this->client;

        $this->newsletter = new Newsletter($this->driver);
    }

    public function tearDown() : void
    {
        parent::tearDown();

        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();
    }

    /** @test */
    public function it_can_subscribe_someone()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $url = 'lists/123/members';

        $this->client->shouldReceive('post')->withArgs([
            $url,
            [
                'email_address' => $email,
                'status' => 'subscribed',
                'email_type' => 'html',
            ],
        ]);

        $this->newsletter->subscribe($email);
    }

    /** @test */
    public function it_will_throw_an_error_when_subscribe_someone()
    {
        $this->client->shouldReceive('success')->andReturn(false);
        $this->client->shouldReceive('getLastError')->andReturn('Error');

        $email = 'freekspatie.be';

        $url = 'lists/123/members';

        $this->client->shouldReceive('post')->withArgs([
            $url,
            [
                'email_address' => $email,
                'status' => 'subscribed',
                'email_type' => 'html',
            ],
        ]);

        $this->expectExceptionObject(ApiError::responseError('Error', 'mailchimp'));
        $this->newsletter->subscribe($email);
    }

    /** @test */
    public function it_can_subscribe_someone_as_pending()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $url = 'lists/123/members';

        $options = [
            'status' => 'pending'
        ];

        $this->client->shouldReceive('post')->withArgs([
            $url,
            [
                'email_address' => $email,
                'status' => 'pending',
                'email_type' => 'html',
            ],
        ]);

        $this->newsletter->subscribe($email, $options);
    }

    /** @test */
    public function it_can_subscribe_or_update_someone()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $url = 'lists/123/members';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client->shouldReceive('put')->withArgs([
            "{$url}/{$subscriberHash}",
            [
                'email_address' => $email,
                'status' => 'subscribed',
                'email_type' => 'html',
            ],
        ]);

        $this->newsletter->subscribeOrUpdate($email);
    }

    /** @test */
    public function it_can_subscribe_someone_with_merge_fields()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $options = [
            'merge_fields' => [
                'FNAME' => 'Freek'
            ]
        ];

        $url = 'lists/123/members';

        $this->client->shouldReceive('post')
            ->once()
            ->withArgs([
                $url,
                [
                    'email_address' => $email,
                    'status' => 'subscribed',
                    'merge_fields' => $options['merge_fields'],
                    'email_type' => 'html',
                ],
            ]);

        $this->newsletter->subscribe($email, $options);
    }

    /** @test */
    public function it_can_subscribe_or_update_someone_with_merge_fields()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $options = [
            'merge_fields' => [
                'FNAME' => 'Freek'
            ]
        ];

        $url = 'lists/123/members';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client->shouldReceive('put')
            ->once()
            ->withArgs([
                "{$url}/{$subscriberHash}",
                [
                    'email_address' => $email,
                    'status' => 'subscribed',
                    'merge_fields' => $options['merge_fields'],
                    'email_type' => 'html',
                ],
            ]);

        $this->newsletter->subscribeOrUpdate($email, $options);
    }

    /** @test */
    public function it_will_throw_an_error_when_subscribe_or_update_someone_with_merge_fields()
    {
        $this->client->shouldReceive('success')->andReturn(false);
        $this->client->shouldReceive('getLastError')->andReturn('Error');

        $email = 'freekspatie.be';

        $options = [
            'merge_fields' => [
                'FNAME' => 'Freek'
            ]
        ];

        $url = 'lists/123/members';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client->shouldReceive('put')
            ->once()
            ->withArgs([
                "{$url}/{$subscriberHash}",
                [
                    'email_address' => $email,
                    'status' => 'subscribed',
                    'merge_fields' => $options['merge_fields'],
                    'email_type' => 'html',
                ],
            ]);

        $this->expectExceptionObject(ApiError::responseError('Error', 'mailchimp'));
        $this->newsletter->subscribeOrUpdate($email, $options);
    }

    /** @test */
    public function it_can_subscribe_someone_to_an_alternative_list()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $url = 'lists/456/members';

        $this->client->shouldReceive('post')
            ->once()
            ->withArgs([
                $url,
                [
                    'email_address' => $email,
                    'status' => 'subscribed',
                    'email_type' => 'html',
                ],
            ]);

        $this->newsletter->subscribe($email, [], 'list2');
    }

    /** @test */
    public function it_can_subscribe_or_update_someone_to_an_alternative_list()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $url = 'lists/456/members';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client->shouldReceive('put')
            ->once()
            ->withArgs([
                "{$url}/{$subscriberHash}",
                [
                    'email_address' => $email,
                    'status' => 'subscribed',
                    'email_type' => 'html',
                ],
            ]);

        $this->newsletter->subscribeOrUpdate($email, [], 'list2');
    }

    /** @test */
    public function it_can_override_the_defaults_when_subscribing_someone()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $url = 'lists/123/members';

        $this->client->shouldReceive('post')
            ->once()
            ->withArgs([
                $url,
                [
                    'email_address' => $email,
                    'status' => 'pending',
                    'email_type' => 'text',
                ],
            ]);

        $this->newsletter->subscribe($email, ['email_type' => 'text', 'status' => 'pending']);
    }

    /** @test */
    public function it_can_override_the_defaults_when_subscribing_or_updating_someone()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $url = 'lists/123/members';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client->shouldReceive('put')
            ->once()
            ->withArgs([
                "{$url}/{$subscriberHash}",
                [
                    'email_address' => $email,
                    'status' => 'pending',
                    'email_type' => 'text',
                ],
            ]);

        $this->newsletter->subscribeOrUpdate($email, ['email_type' => 'text', 'status' => 'pending']);
    }


    /** @test */
    public function it_can_unsubscribe_someone()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('patch')
            ->once()
            ->withArgs([
                "lists/123/members/{$subscriberHash}",
                [
                    'status' => 'unsubscribed',
                ],
            ]);

        $this->newsletter->unsubscribe('freek@spatie.be');
    }

    /** @test */
    public function it_will_throw_an_error_when_unsubscribe_someone()
    {
        $this->client->shouldReceive('success')->andReturn(false);
        $this->client->shouldReceive('getLastError')->andReturn('Error');

        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('patch')
            ->once()
            ->withArgs([
                "lists/123/members/{$subscriberHash}",
                [
                    'status' => 'unsubscribed',
                ],
            ]);

        $this->expectExceptionObject(ApiError::responseError('Error', 'mailchimp'));
        $this->newsletter->unsubscribe('freek@spatie.be');
    }

    /** @test */
    public function it_can_unsubscribe_someone_from_a_specific_list()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('patch')
            ->once()
            ->withArgs([
                "lists/456/members/{$subscriberHash}",
                [
                    'status' => 'unsubscribed',
                ],
            ]);

        $this->newsletter->unsubscribe('freek@spatie.be', 'list2');
    }

    /** @test */
    public function it_can_delete_someone()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('delete')
            ->once()
            ->withArgs(["lists/123/members/{$subscriberHash}"]);

        $this->newsletter->delete('freek@spatie.be');
    }

    /** @test */
    public function it_can_delete_someone_from_a_specific_list()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('delete')
            ->once()
            ->withArgs(["lists/456/members/{$subscriberHash}"]);

        $this->newsletter->delete('freek@spatie.be', 'list2');
    }

    /** @test */
    public function it_exposes_the_api()
    {
        $api = $this->newsletter->getApi();

        $this->assertSame($this->client, $api);
    }

    /** @test */
    public function it_can_get_the_list_members()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $this->client
            ->shouldReceive('get')
            ->once()
            ->withArgs(['lists/123/members', []]);

        $this->newsletter->getMembers();
    }

    /** @test */
    public function it_can_get_the_member()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('get')
            ->once()
            ->withArgs(["lists/123/members/{$subscriberHash}"]);

        $this->newsletter->getMember($email);
    }


    /** @test */
    public function it_can_get_the_member_from_a_specific_list()
    {
        $this->client->shouldReceive('success')->andReturn(true);

        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('get')
            ->once()
            ->withArgs(["lists/456/members/{$subscriberHash}"]);

        $this->newsletter->getMember($email, 'list2');
    }
}
