<?php

use Flarum\Database\Migration;

return Migration::addColumns('discussions', [
    'mailbox_thread_id' => ['string', 'length' => 255, 'nullable' => true],
]);
