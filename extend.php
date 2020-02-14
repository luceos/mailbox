<?php

namespace FoF\Mailbox;

use Flarum\Extend\Frontend;
use Flarum\Extend\Locales;

return [
    new Locales(__DIR__ . '/resources/locale'),

    (new Frontend('admin'))
        ->js(__DIR__  . '/js/dist/admin.js')
];
