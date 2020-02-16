<?php

namespace FoF\Mailbox\Models;

use Ddeboer\Imap\Message as Imap;
use Illuminate\Support\Arr;

/**
 * @mixin Imap
 */
class Message
{
    /** @var Imap  */
    private $imap;

    /** @var bool */
    public $isFirst;

    /** @var string */
    public $reference;

    public function __construct(Imap $imap)
    {
        $this->imap = $imap;
        $this->isFirst = $imap->getNumber() === 1;
        $this->reference = $this->getReference();
    }

    protected function getReference()
    {
        $references = $this->getHeaders()->get('references');
        if ($references && strlen($references) > 0) {
            return explode(' ', $references);
        }

        return null;
    }

    public function validForImport()
    {
        return $this->reference || $this->getId();
    }

    public function __get($name)
    {
        return $this->imap->{$name};
    }

    public function __call($name, $arguments)
    {
        return $this->imap->{$name}($arguments);
    }
}
