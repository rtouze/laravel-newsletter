<?php

namespace DansMaCulotte\Newsletter\Drivers;

use DrewM\MailChimp\MailChimp;
use Exception;
use DansMaCulotte\Newsletter\Exceptions\ApiError;
use DansMaCulotte\Newsletter\Exceptions\InvalidNewsletterList;
use DansMaCulotte\Newsletter\NewsletterListCollection;

class MailchimpDriver implements Driver
{
    /** @var MailChimp */
    public $client;

    /** @var NewsletterListCollection */
    public $lists;

    /**
     * MailchimpDriver constructor.
     * @param array $credentials
     * @param array $config
     * @throws InvalidNewsletterList
     */
    public function __construct(array $credentials, array $config)
    {
        $this->client = new Mailchimp($credentials['apiKey']);
        $this->lists = NewsletterListCollection::createFromConfig($config);
    }


    /**
     * @param string $email
     * @param array $options
     * @param string $listName
     * @return array|false
     * @throws ApiError
     * @throws InvalidNewsletterList
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
     * @throws InvalidNewsletterList
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
     * @throws InvalidNewsletterList
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
     * @throws InvalidNewsletterList
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
     * @throws InvalidNewsletterList
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
     * @throws InvalidNewsletterList
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
     * @throws InvalidNewsletterList
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
     * @throws InvalidNewsletterList
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
