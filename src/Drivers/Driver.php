<?php

namespace DansMaCulotte\Newsletter\Drivers;

interface Driver
{
    public function __construct(array $credentials, array $config);

    public function subscribe(string $email, array $options = [], string $listName = '');

    public function subscribeOrUpdate(string $email, array $options = [], string $listName = '');

    public function addMember(string $email, array $options = [], string $listName = '');

    public function getMembers(string $listName = '', array $parameters = []);

    public function getMember(string $email, string $listName = '');

    public function hasMember(string $email, string $listName = ''): bool;

    public function isSubscribed(string $email, string $listName = ''): bool;

    public function unsubscribe(string $email, string $listName = '');

    public function delete(string $email, string $listName = '');

    public function getLastError();

    public function getApi();
}
