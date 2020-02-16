<?php

namespace FoF\Mailbox;

use Flarum\Extend\Frontend;
use Flarum\Extend\Locales;
use Illuminate\Contracts\Events\Dispatcher;

return [
    new Locales(__DIR__ . '/resources/locale'),

    (new Frontend('admin'))
        ->js(__DIR__  . '/js/dist/admin.js'),

    new Extend\Command(Commands\MailboxWorkerCommand::class),

    function (Dispatcher $events) {
        $events->subscribe(Listeners\SaveTagFields::class);
        $events->subscribe(Outbound\EmailOnReply::class);
    }
];
