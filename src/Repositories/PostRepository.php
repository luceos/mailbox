<?php

namespace FoF\Mailbox\Repositories;

use Flarum\Post\CommentPost;

class PostRepository
{
    /**
     * @var CommentPost
     */
    private $posts;

    public function __construct(CommentPost $posts)
    {
        $this->posts = $posts;
    }

    public function forEmailId(string $id, int $number): CommentPost
    {
        $post = $this->posts->query()
            ->where('mailbox_thread_id', $id)
            ->where('mailbox_email_number', $number)
            ->first();

        if (! $post) {
            $post = $this->posts->newInstance();
            $post->mailbox_thread_id = $id;
            $post->mailbox_email_number = $number;
        }

        return $post;
    }
}
