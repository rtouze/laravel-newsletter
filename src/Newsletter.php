<?php

namespace DansMaCulotte\Newsletter;

use DansMaCulotte\Newsletter\Drivers\Driver;

class Newsletter
{
    /** @var Driver|null */
    public $driver = null;

    /**
     * MailTemplate constructor.
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }


    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        return $this->driver->$name(...$arguments);
    }
}
