<?php

namespace DansMaCulotte\Newsletter\Test\Mailjet;

use Mailjet\Client;
use Mailjet\Resources;
use Mailjet\Response;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use DansMaCulotte\Newsletter\Drivers\MailjetDriver;
use DansMaCulotte\Newsletter\Exceptions\ApiError;
use DansMaCulotte\Newsletter\Newsletter;

class NewsletterTest extends TestCase
{
    /** @var MailjetDriver */
    protected $driver;

    /** @var Mockery\Mock */
    protected $client;

    /** @var \DansMaCulotte\Newsletter\Newsletter */
    protected $newsletter;

    /** @var Mock */
    protected $response;


    public function setUp() : void
    {
        $this->client = Mockery::mock(Client::class);

        $this->driver = new MailjetDriver([
            'key' => 'apikey',
            'secret' => 'apisecret',
        ], [
            'lists' => [
                'list1' => ['id' => 123],
                'list2' => ['id' => 456],
            ],
            'defaultList' => 'list1',
            'connection_timeout' => 20,
        ]);

        $this->driver->client = $this->client;


        $this->response = Mockery::mock(Response::class);
        $this->response->shouldReceive('getData')->andReturn([]);
        $this->response->shouldReceive('getReasonPhrase')->andReturn('Error');
        $this->response->shouldReceive('getStatus')->andReturn('400');

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
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'test@test.fr',
                    'Action' => 'addforce',
                    'Name' => 'Martin',
                ]
            ]

        ])->andReturn($this->response);

        $this->newsletter->subscribe('test@test.fr', ['Name' => 'Martin']);
    }

    /** @test */
    public function it_can_subscribe_or_update_someone()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'test@test.fr',
                    'Action' => 'addforce',
                    'Name' => 'Martin',
                ]
            ]

        ])->andReturn($this->response);

        $this->newsletter->subscribeOrUpdate('test@test.fr', ['Name' => 'Martin']);
    }

    /** @test */
    public function it_will_trow_an_exception_when_subscribe_with_an_invalid_email()
    {
        $this->response->shouldReceive('success')->andReturn(false);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'testtest.fr',
                    'Action' => 'addforce',
                    'Name' => 'Martin',
                ]
            ]
        ])->andReturn($this->response);

        $this->expectExceptionObject(ApiError::responseError('Error', 'mailjet', 400));
        $this->newsletter->subscribe('testtest.fr', ['Name' => 'Martin']);
    }

    /** @test */
    public function it_can_get_the_list_members()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$Contact,
            [
                'ContactsList' => '123'
            ]
        ])->andReturn($this->response);

        $this->newsletter->getMembers();
    }

    /** @test */
    public function it_will_trow_an_exceptions_when_get_members()
    {
        $this->response->shouldReceive('success')->andReturn(false);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$Contact,
            [
                'ContactsList' => '123'
            ]
        ])->andReturn($this->response);


        $this->expectExceptionObject(ApiError::responseError('Error', 'mailjet', 400));
        $this->newsletter->getMembers();
    }

    /** @test */
    public function it_can_get_the_member()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$Contact,
            [
                'id' => 'test@test.fr'
            ]
        ])->andReturn($this->response);

        $this->newsletter->getMember('test@test.fr');
    }

    /** @test */
    public function it_will_trow_an_exception_when_get_the_member()
    {
        $this->response->shouldReceive('success')->andReturn(false);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$Contact,
            [
                'id' => 'testtest.fr'
            ]
        ])->andReturn($this->response);


        $this->expectExceptionObject(ApiError::responseError('Error', 'mailjet', 400));
        $this->newsletter->getMember('testtest.fr');
    }

    /** @test */
    public function it_can_unsubscribe_someone()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'test@test.fr',
                    'Action' => 'unsub',
                ]
            ]

        ])->andReturn($this->response);
        $this->newsletter->unsubscribe('test@test.fr');
    }

    /** @test */
    public function it_will_trow_an_exception_when_unsubscribe_someone()
    {
        $this->response->shouldReceive('success')->andReturn(false);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'testtest.fr',
                    'Action' => 'unsub',
                ]
            ]

        ])->andReturn($this->response);

        $this->expectExceptionObject(ApiError::responseError('Error', 'mailjet', 400));
        $this->newsletter->unsubscribe('testtest.fr');
    }

    /** @test */
    public function it_can_delete_someone()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'test@test.fr',
                    'Action' => 'remove',
                ]
            ]

        ])->andReturn($this->response);

        $this->newsletter->delete('test@test.fr');
    }

    /** @test */
    public function it_will_trow_an_exception_when_delete_someone()
    {
        $this->response->shouldReceive('success')->andReturn(false);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'test@test.fr',
                    'Action' => 'remove',
                ]
            ]

        ])->andReturn($this->response);


        $this->expectExceptionObject(ApiError::responseError('Error', 'mailjet', 400));
        $this->newsletter->delete('test@test.fr');
    }

    /** @test */
    public function it_exposes_the_api()
    {
        $api = $this->newsletter->getApi();

        $this->assertSame($this->client, $api);
    }

    /** @test */
    public function it_can_check_if_member_is_subscribed()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('success')->andReturn(true);
        $response->shouldReceive('getData')->andReturn([
            [
                'ListID' => 123,
                'IsUnsub' => false
            ]
        ]);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$ContactGetcontactslists,
            [
                'id' => 'test@test.fr'
            ]
        ])->andReturn($response);


        $response = $this->newsletter->isSubscribed('test@test.fr');
        $this->assertTrue($response);
    }

    /** @test */
    public function it_can_check_if_member_is_not_subscribed()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('success')->andReturn(true);
        $response->shouldReceive('getData')->andReturn([
            [
                'ListID' => 124,
                'IsUnsub' => false
            ]
        ]);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$ContactGetcontactslists,
            [
                'id' => 'test@test.fr'
            ]
        ])->andReturn($response);


        $response = $this->newsletter->isSubscribed('test@test.fr');
        $this->assertFalse($response);
    }

    /** @test */
    public function it_will_trow_an_exception_when_check_if_member_is_subscribed()
    {
        $this->response->shouldReceive('success')->andReturn(false);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$ContactGetcontactslists,
            [
                'id' => 'testtest.fr'
            ]
        ])->andReturn($this->response);

        $this->expectExceptionObject(ApiError::responseError('Error', 'mailjet', 400));
        $this->newsletter->isSubscribed('testtest.fr');
    }

    /** @test */
    public function it_can_check_if_list_has_member()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('success')->andReturn(true);
        $response->shouldReceive('getData')->andReturn([
            [
                'Email' => 'test@test.fr',
            ]
        ]);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$Contact,
            [
                'ContactsList' => '123'
            ]
        ])->andReturn($response);

        $response = $this->newsletter->hasMember('test@test.fr');
        $this->assertTrue($response);
    }


    /** @test */
    public function it_can_check_if_list_does_not_have_member()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$Contact,
            [
                'ContactsList' => '123'
            ]
        ])->andReturn($this->response);

        $response = $this->newsletter->hasMember('test@test.fr');
        $this->assertFalse($response);
    }

    /** @test */
    public function it_will_trow_an_exception_when_check_if_list_has_member()
    {
        $this->response->shouldReceive('success')->andReturn(false);

        $this->client->shouldReceive('get')->withArgs([
            Resources::$Contact,
            [
                'ContactsList' => '123'
            ]
        ])->andReturn($this->response);

        $this->expectExceptionObject(ApiError::responseError('Error', 'mailjet', 400));
        $this->newsletter->hasMember('test@test.fr');
    }

    /** @test */
    public function it_can_add_member_to_a_list()
    {
        $this->response->shouldReceive('success')->andReturn(true);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'test@test.fr',
                    'Action' => 'addforce',
                ]
            ]

        ])->andReturn($this->response);

        $this->client->shouldReceive('post')->withArgs([
            Resources::$ContactslistManagecontact,[
                'id' => '123',
                'body' => [
                    'Email' => 'test@test.fr',
                    'Action' => 'unsub',
                ]
            ]

        ])->andReturn($this->response);

        $this->newsletter->addMember('test@test.fr');
    }
}
