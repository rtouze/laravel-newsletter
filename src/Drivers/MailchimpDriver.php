<?php

namespace Spatie\Newsletter\Drivers;

use DrewM\MailChimp\MailChimp;
use Spatie\Newsletter\Exceptions\ApiError;
use Spatie\Newsletter\NewsletterListCollection;

class MailchimpDriver implements Driver
{
    /** @var \DrewM\MailChimp\MailChimp */
    public $client;

    /** @var \Spatie\Newsletter\NewsletterListCollection */
    public $lists;

    /**
     * MailchimpDriver constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->client = new Mailchimp($config['apiKey']);
        $this->client->verify_ssl = $config['ssl'];
        $this->lists = NewsletterListCollection::createFromConfig($config);
    }


    /**
     * @param string $email
     * @param array $options
     * @param string $listName
     * @return array|false
     * @throws ApiError
     */
    public function subscribe(string $email, array $options = [], string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $options = $this->getSubscriptionOptions($email, $options);

        $response = $this->client->post("lists/{$list->getId()}/members", $options);

        if (! $this->client->success()) {
            throw ApiError::responseError($this->client->getLastError(), 'mailchimp');
        }

        return $response;
    }


    /**
     * @param string $email
     * @param array $options
     * @param string $listName
     * @return array|bool|false
     * @throws ApiError
     */
    public function subscribeOrUpdate(string $email, array $options = [], string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $options = $this->getSubscriptionOptions($email, $options);

        $response = $this->client->put("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", $options);

        if (! $this->client->success()) {
            throw ApiError::responseError($this->client->getLastError(), 'mailchimp');
        }

        return $response;
    }

    /**
     * @param string $listName
     * @param array $parameters
     * @return array|false
     */
    public function getMembers(string $listName = '', array $parameters = [])
    {
        $list = $this->lists->findByName($listName);

        return $this->client->get("lists/{$list->getId()}/members", $parameters);
    }

    /**
     * @param string $email
     * @param string $listName
     * @return array|false
     */
    public function getMember(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        return $this->client->get("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");
    }

    /**
     * @param string $email
     * @param string $listName
     * @return bool
     */
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

    /**
     * @param string $email
     * @param string $listName
     * @return bool
     */
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

    /**
     * @param string $email
     * @param string $listName
     * @return array|false
     * @throws ApiError
     */
    public function unsubscribe(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->client->patch("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", [
            'status' => 'unsubscribed',
        ]);

        if (! $this->client->success()) {
            throw ApiError::responseError($this->client->getLastError(), 'mailchimp');
        }

        return $response;
    }


    /**
     * @param string $email
     * @param string $listName
     * @return array|false
     */
    public function delete(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->client->delete("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");

        return $response;
    }

    /**
     * @return MailChimp
     */
    public function getApi(): MailChimp
    {
        return $this->client;
    }

    /**
     * @param string $email
     * @return string
     */
    protected function getSubscriberHash(string $email): string
    {
        return $this->client->subscriberHash($email);
    }

    /**
     * @param string $email
     * @param array $options
     * @return array
     */
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