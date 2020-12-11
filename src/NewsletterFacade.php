<?php

namespace DansMaCulotte\Newsletter;

use Illuminate\Support\Facades\Facade;

/**
 * @see Newsletter
 * @method static subscribe(string $email, array $options = [], string $listName = '')
 * @method static subscribeOrUpdate(string $email, array $options = [], string $listName = '')
 * @method static addMember(string $email, array $options = [], string $listName = '')
 * @method static getMembers(string $listName, array $parameters = [])
 * @method static getMember(string $email, string $listName = '')
 * @method static bool hasMember(string $email, string $listName = '')
 * @method static bool isSubscribed(string $email, string $listName = '')
 * @method static unsubscribe(string $email, string $listName = '')
 * @method static delete(string $email, string $listName = '')
 * @method static getLastError()
 * @method static getApi()
 */
class NewsletterFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'newsletter';
    }
}
