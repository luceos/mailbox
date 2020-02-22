<?php

namespace FoF\Mailbox\Events;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use FoF\Mailbox\Models\Message;

class EmailProcessed
{
    /**
     * @var Message
     */
    public $message;
    /**
     * @var Discussion
     */
    public $discussion;
    /**
     * @var Post
     */
    public $post;

    public function __construct(Message $message, Discussion &$discussion, Post &$post)
    {
        $this->message = &$message;
        $this->discussion = &$discussion;
        $this->post = &$post;
    }
}
