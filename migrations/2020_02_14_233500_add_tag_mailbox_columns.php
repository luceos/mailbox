<?php

use Flarum\Database\Migration;

return Migration::addColumns('tags', [
    'mailbox_enabled' => ['boolean', 'default' => 0],
    'mailbox_imap_host' => ['string', 'length' => 255, 'nullable' => true],
    'mailbox_imap_port' => ['string', 'length' => 20, 'nullable' => true],
    'mailbox_imap_username' => ['string', 'length' => 100, 'nullable' => true],
    'mailbox_imap_password' => ['string', 'length' => 100, 'nullable' => true]
]);
