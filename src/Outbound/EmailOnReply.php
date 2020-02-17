<?php

namespace FoF\Mailbox\Outbound;

use Flarum\Post\Event\Posted;
use FoF\Mailbox\Models\Mailbox;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Mail\Mailer;
use Swift_Message;

class EmailOnReply
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Posted::class, [$this, 'posted']);
    }

    public function posted(Posted $event)
    {
        $id = $event->post->discussion->mailbox_thread_id;
        // Identify whether discussion is mailbox enabled.
        if ($id === null) {
            return;
        }

        // Identify whether mailbox is enabled.
        $tag = $event->post->discussion->tags()
            ->where('mailbox_enabled', true)
            ->first();

        $recipient = $event->post->discussion->firstPost->user;

        $mailbox = Mailbox::asTag($tag);

        $swift = $mailbox->swift();
        $message = new Swift_Message($event->post->discussion->title, $event->post->content);
        $message->getHeaders()->addParameterizedHeader('references', $id);
        $message->setFrom($mailbox->sender);
        $message->setTo($recipient->email, $recipient->username);
        $message->setId(substr($id, 1, -1));
        $swift->send($message);
    }

    protected function mailer(): Mailer
    {
        return app(Mailer::class);
    }
}
