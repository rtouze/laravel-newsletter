<?php

namespace DansMaCulotte\Newsletter\Drivers;

use Mailjet\Client;
use Mailjet\Response;
use DansMaCulotte\Newsletter\Exceptions\ApiError;
use DansMaCulotte\Newsletter\Exceptions\InvalidNewsletterList;
use DansMaCulotte\Newsletter\NewsletterListCollection;
use Mailjet\Resources;

class MailjetDriver implements Driver
{

    /** @var Client */
    public $client;

    /** @var NewsletterListCollection */
    public $lists;

    public function __construct(array $credentials, array $config)
    {
        $this->client = new Client($credentials['key'], $credentials['secret']);
        $this->lists = NewsletterListCollection::createFromConfig($config);
    }

    /**
     * @param string $email
     * @param array $options
     * @param string $listName
     * @return Response
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function subscribe(string $email, array $options = [], string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $body = [
            'Email' => $email,
            'Action' => 'addforce',
        ];

        $body = array_merge($body, $options);

        $response = $this->client->post(Resources::$ContactslistManagecontact, ['id' => $list->getId(), 'body' => $body]);

        if (! $response->success()) {
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        return $response;
    }

    /**
     * @param string $email
     * @param array $options
     * @param string $listName
     * @return Response
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function subscribeOrUpdate(string $email, array $options = [], string $listName = '')
    {
        return $this->subscribe($email, $options, $listName);
    }

    /**
     * @param string $email
     * @param array $options
     * @param string $listName
     * @return Response
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function addMember(string $email, array $options = [], string $listName = '')
    {
        $response = $this->subscribe($email, $options, $listName);

        if (!$response->success()) {
            $this->lastError = $response->getData();
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        return $this->unsubscribe($email, $listName);
    }

    /**
     * @param string $listName
     * @param array $parameters
     * @return array
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function getMembers(string $listName = '', array $parameters = [])
    {
        $listId = $this->lists->findByName($listName)->getId();

        $body = [
            'ContactsList' => $listId
        ];

        $body = array_merge($body, $parameters);

        $response = $this->client->get(Resources::$Contact, $body);

        if (! $response->success()) {
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        return $response->getData();
    }

    /**
     * @param string $email
     * @param string $listName
     * @return array
     * @throws ApiError
     */
    public function getMember(string $email, string $listName = '')
    {
        $response = $this->client->get(Resources::$Contact, ['id' => $email]);

        if (! $response->success()) {
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        return $response->getData();
    }


    /**
     * @param string $email
     * @param string $listName
     * @return bool
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function hasMember(string $email, string $listName = ''): bool
    {
        $listId = $this->lists->findByName($listName)->getId();

        $response = $this->client->get(Resources::$Contact, ['ContactsList' => $listId]);

        if (! $response->success()) {
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        $contacts = $response->getData();

        foreach ($contacts as $contact) {
            if ($contact['Email'] === $email) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @param string $listName
     * @return bool
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function isSubscribed(string $email, string $listName = ''): bool
    {
        $listId = $this->lists->findByName($listName)->getId();

        $response = $this->client->get(Resources::$ContactGetcontactslists, ['id' => $email]);

        if (! $response->success()) {
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        $contactLists = $response->getData();

        foreach ($contactLists as $list) {
            if ((string)$list['ListID'] === $listId && $list['IsUnsub'] !== true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @param string $listName
     * @return Response
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function unsubscribe(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $body = [
            'Email' => $email,
            'Action' => 'unsub',
        ];

        $response = $this->client->post(Resources::$ContactslistManagecontact, ['id' => $list->getId(), 'body' => $body]);

        if (! $response->success()) {
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        return $response;
    }


    /**
     * @param string $email
     * @param string $listName
     * @return Response
     * @throws ApiError
     * @throws InvalidNewsletterList
     */
    public function delete(string $email, string $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $body = [
            'Email' => $email,
            'Action' => 'remove',
        ];

        $response = $this->client->post(Resources::$ContactslistManagecontact, [
                'id' => $list->getId(),
                'body' => $body
            ]
        );

        if (! $response->success()) {
            throw ApiError::responseError($response->getReasonPhrase(), 'mailjet', $response->getStatus());
        }

        return $response;
    }

    public function getApi()
    {
        return $this->client;
    }
}
