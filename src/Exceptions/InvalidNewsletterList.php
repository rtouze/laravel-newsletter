<?php

namespace DansMaCulotte\Newsletter\Exceptions;

use Exception;

class InvalidNewsletterList extends Exception
{
    /**
     * @return static
     */
    public static function noListsDefined()
    {
        return new static('There are no lists defined.');
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function noListWithName($name)
    {
        return new static("There is no list named `{$name}`.");
    }

    /**
     * @param $defaultList
     *
     * @return static
     */
    public static function defaultListDoesNotExist($defaultList)
    {
        return new static("Could not find a default list named `{$defaultList}`.");
    }
}
