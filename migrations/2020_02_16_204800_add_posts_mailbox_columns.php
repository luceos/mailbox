<?php

use Flarum\Database\Migration;

return Migration::addColumns('posts', [
    'mailbox_thread_id' => ['string', 'length' => 255, 'nullable' => true],
    'mailbox_email_number' => ['int', 'length' => 10, 'nullable' => true],
]);
