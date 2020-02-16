<?php

namespace FoF\Mailbox\Listeners;

use Flarum\Api\Event\Serializing;
use Flarum\Tags\Api\Serializer\TagSerializer;
use Flarum\Tags\Event\TagWillBeSaved;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SaveTagFields
{
    protected $fields = [
        'mailboxEnabled', 'mailboxSender',
        'mailboxImapHost', 'mailboxImapPort', 'mailboxImapEncryption',
        'mailboxSmtpPort',
        'mailboxImapUsername', 'mailboxImapPassword'
    ];

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Serializing::class, [$this, 'readAttributes']);
        $events->listen(TagWillBeSaved::class, [$this, 'saveAttributes']);
    }

    public function readAttributes(Serializing $event)
    {
        if ($event->isSerializer(TagSerializer::class)) {
            $attributes = Arr::only($event->model->getAttributes(), $this->snakeFields());

            $event->attributes = array_merge($event->attributes, $attributes);
        }
    }

    public function saveAttributes(TagWillBeSaved $event)
    {
        $attributes = Arr::only($event->data['attributes'], $this->fields);
dd($attributes);
        foreach ($attributes as $field => $value) {
            $property = Str::snake($field);
            $event->tag->{$property} = $value;
        }
    }

    protected function snakeFields(): array
    {
        $fields = $this->fields;

        array_walk($fields, function (&$value) {
            $value = Str::snake($value);
        });

        return $fields;
    }
}
