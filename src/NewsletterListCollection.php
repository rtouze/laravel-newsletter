<?php

namespace DansMaCulotte\Newsletter;

use Illuminate\Support\Collection;
use DansMaCulotte\Newsletter\Exceptions\InvalidNewsletterList;

class NewsletterListCollection extends Collection
{
    /** @var string */
    public $defaultList = '';

    public static function createFromConfig(array $config): self
    {
        $collection = new static();

        if (!count($config['lists']))
        {
            throw InvalidNewsletterList::noListsDefined();
        }

        foreach ($config['lists'] as $name => $listProperties) {
            $collection->push(new NewsletterList($name, $listProperties));
        }

        $collection->defaultList = $config['defaultList'];

        return $collection;
    }

    public function findByName(string $name): NewsletterList
    {
        if ($name === '') {
            return $this->getDefault();
        }

        foreach ($this->items as $newsletterList) {
            if ($newsletterList->getName() === $name) {
                return $newsletterList;
            }
        }

        throw InvalidNewsletterList::noListWithName($name);
    }

    public function getDefault(): NewsletterList
    {
        foreach ($this->items as $newsletterList) {
            if ($newsletterList->getName() === $this->defaultList) {
                return $newsletterList;
            }
        }

        throw InvalidNewsletterList::defaultListDoesNotExist($this->defaultList);
    }
}
