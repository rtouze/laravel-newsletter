<?php

namespace Spatie\Newsletter\Drivers;

use DrewM\MailChimp\MailChimp;
use Spatie\Newsletter\NewsletterListCollection;

class MailchimpDriver implements Driver
{
    /** @var \DrewM\MailChimp\MailChimp */
    public $client;

    /** @var \Spatie\Newsletter\NewsletterListCollection */
    public $lists;

    public function __construct(array $config)
    {
        $this->client = new Mailchimp($config['apiKey']);
        $this->client->verify_ssl = $config['ssl'];
        $this->lists = NewsletterListCollection::createFromConfig($config);
    }


    public function subscribe(string $email, array $options = [], string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $options = $this->getSubscriptionOptions($email, $options);

        $response = $this->client->post("lists/{$list->getId()}/members", $options);

        if (! $this->client->success()) {
            return false;
        }

        return $response;
    }


    public function subscribeOrUpdate(string $email, array $options = [], string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $options = $this->getSubscriptionOptions($email, $options);

        $response = $this->client->put("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", $options);

        if (! $this->client->success()) {
            return false;
        }

        return $response;
    }

    public function getMembers(string $listName = '', array $parameters = [])
    {
        $list = $this->lists->findByName($listName);

        return $this->client->get("lists/{$list->getId()}/members", $parameters);
    }

    public function getMember(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        return $this->client->get("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");
    }

    public function getMemberActivity(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        return $this->client->get("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}/activity");
    }

    public function hasMember(string $email, string $listName = ''): bool
    {
        $response = $this->getMember($email, $listName);

        if (! isset($response['email_address'])) {
            return false;
        }

        if (strtolower($response['email_address']) != strtolower($email)) {
            return false;
        }

        return true;
    }

    public function isSubscribed(string $email, string $listName = ''): bool
    {
        $response = $this->getMember($email, $listName);

        if (! isset($response)) {
            return false;
        }

        if ($response['status'] != 'subscribed') {
            return false;
        }

        return true;
    }

    public function unsubscribe(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->client->patch("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", [
            'status' => 'unsubscribed',
        ]);

        if (! $this->client->success()) {
            return false;
        }

        return $response;
    }

    public function updateEmailAddress(string $currentEmailAddress, string $newEmailAddress, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->client->patch("lists/{$list->getId()}/members/{$this->getSubscriberHash($currentEmailAddress)}", [
            'email_address' => $newEmailAddress,
        ]);

        return $response;
    }

    public function delete(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->client->delete("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");

        return $response;
    }

    public function getApi(): MailChimp
    {
        return $this->client;
    }

    protected function getSubscriberHash(string $email): string
    {
        return $this->client->subscriberHash($email);
    }

    protected function getSubscriptionOptions(string $email, array $options): array
    {
        $defaultOptions = [
            'email_address' => $email,
            'status' => 'subscribed',
            'email_type' => 'html',
        ];

        $options = array_merge($defaultOptions, $options);

        return $options;
    }
}