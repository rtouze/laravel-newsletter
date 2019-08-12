<?php

namespace Spatie\Newsletter\Test;

use Mockery;
use DrewM\MailChimp\MailChimp;
use PHPUnit\Framework\TestCase;
use Spatie\Newsletter\Drivers\MailchimpDriver;
use Spatie\Newsletter\Newsletter;
use Spatie\Newsletter\NewsletterListCollection;

class NewsletterTest extends TestCase
{
    protected $driver;

    /** @var Mockery\Mock */
    protected $client;

    /** @var \Spatie\Newsletter\Newsletter */
    protected $newsletter;

    public function setUp() : void
    {
        $this->client = Mockery::mock(MailChimp::class);

        $this->driver = new MailchimpDriver([
            'apiKey' => 'test-ApiKey',
            'ssl' => false,
            'lists' => [
                'list1' => ['id' => 123],
                'list2' => ['id' => 456],
            ],
            'defaultListName' => 'list1',
        ]);

        $this->driver->client = $this->client;
        $this->client->shouldReceive('success')->andReturn(true);

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
    public function it_can_subscribe_someone_as_pending()
    {
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
    public function it_can_subscribe_someone_to_an_alternative_list()
    {
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
    public function it_can_change_the_email_address_of_a_subscriber()
    {
        $email = 'freek@spatie.be';
        $newEmail = 'phreak@spatie.be';

        $url = 'lists/123/members';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('patch')
            ->once()
            ->withArgs([
                "{$url}/{$subscriberHash}",
                [
                    'email_address' => $newEmail,
                ],
            ]);

        $this->newsletter->updateEmailAddress($email, $newEmail);
    }

    /** @test */
    public function it_can_unsubscribe_someone()
    {
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
    public function it_can_unsubscribe_someone_from_a_specific_list()
    {
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
        $this->client
            ->shouldReceive('get')
            ->once()
            ->withArgs(['lists/123/members', []]);

        $this->newsletter->getMembers();
    }

    /** @test */
    public function it_can_get_the_member()
    {
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
    public function it_can_get_the_member_activity()
    {
        $email = 'freek@spatie.be';

        $subscriberHash = 'abc123';

        $this->client->shouldReceive('subscriberHash')
            ->once()
            ->withArgs([$email])
            ->andReturn($subscriberHash);

        $this->client
            ->shouldReceive('get')
            ->once()
            ->withArgs(["lists/123/members/{$subscriberHash}/activity"]);

        $this->newsletter->getMemberActivity($email);
    }

    /** @test */
    public function it_can_get_the_member_from_a_specific_list()
    {
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
