<?php

namespace FoF\Mailbox\Repositories;

use Flarum\Discussion\Discussion;

class DiscussionRepository
{
    /**
     * @var Discussion
     */
    private $discussions;

    public function __construct(Discussion $discussions)
    {
        $this->discussions = $discussions;
    }

    public function forThreadId(string $id): Discussion
    {
        $discussion = $this->discussions->query()
            ->where('mailbox_thread_id', $id)
            ->first();

        if (! $discussion) {
            $discussion = $this->discussions->newInstance();
            $discussion->mailbox_thread_id = $id;
        }

        return $discussion;
    }
}
