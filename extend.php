<?php

namespace FoF\Mailbox;

use Flarum\Extend\Frontend;
use Flarum\Extend\Locales;
use FoF\Console\Extend\EnableConsole;
use FoF\Console\Extend\ScheduleCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;

return [
    new Locales(__DIR__ . '/resources/locale'),

    (new Frontend('admin'))
        ->js(__DIR__  . '/js/dist/admin.js'),

    new Extend\Command(Commands\MailboxWorkerCommand::class),

    function (Dispatcher $events) {
        $events->subscribe(Listeners\SaveTagFields::class);
        $events->subscribe(Outbound\EmailOnReply::class);
    },

    new EnableConsole,
    new ScheduleCommand(function (Schedule $schedule) {
        $schedule->command(Commands\MailboxWorkerCommand::class)
            ->everyMinute()
            ->onOneServer()
            ->withoutOverlapping();
    })
];
