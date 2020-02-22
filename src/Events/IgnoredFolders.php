<?php

namespace FoF\Mailbox\Events;

class IgnoredFolders
{
    /**
     * @var array
     */
    public $folders;

    public function __construct(array &$defaults = [])
    {
        $this->folders = &$defaults;
    }

    public function override(array $folders = [])
    {
        $this->folders = $folders;

        return $this;
    }
}
