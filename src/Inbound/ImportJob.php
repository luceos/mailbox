<?php

namespace FoF\Mailbox\Inbound;

use Flarum\Discussion\Event\Started;
use Flarum\Post\Event\Posted;
use Flarum\User\User;
use Flarum\User\UserRepository;
use FoF\Mailbox\Events\EmailProcessed;
use FoF\Mailbox\Events\EmailReceived;
use FoF\Mailbox\Models\Mailbox;
use FoF\Mailbox\Models\Message;
use FoF\Mailbox\Repositories\DiscussionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Mailbox
     */
    private $mailbox;
    /**
     * @var Message
     */
    private $message;

    public function __construct(Mailbox $mailbox, Message $message)
    {
        $this->mailbox = $mailbox;
        $this->message = $message;
    }

    public function handle(DiscussionRepository $discussions)
    {
        $discussion = $post = null;

        if (empty($this->message->getBodyText())) return;

        $user = $this->importUser($this->message);

        if ($this->message->reference) {
            foreach ($this->message->reference as $ref) {
                $discussion = $discussions->forThreadId($ref);

                // Stop when having identified an existing, persisted discussion.
                if ($discussion->exists) break;
            }
        } else {
            $discussion = $discussions->forThreadId($this->message->getId());
        }

        $this->events->dispatch(new EmailReceived($this->message, $discussion, $post));

        if (! $discussion->exists) {
            $discussion->title = $this->message->getSubject();

            $discussion->save();

            $discussion->raise(new Started($discussion));
        }

        $discussion->tags()->sync($this->mailbox->tag);

        if (! $post) {
            $post = $this->posts->forEmailId($this->message->getId(), $this->message->getNumber());
        }

        $post->content = $this->message->getBodyText();
        $post->discussion()->associate($discussion);
        $post->user()->associate($user);
        $post->created_at = $this->message->getDate()->setTimezone(new DateTimeZone('UTC'));;
        $post->save();

        if ($this->message->isFirst || $discussion->first_post_id === null) {
            $discussion->created_at = $this->message->getDate()->setTimezone(new DateTimeZone('UTC'));
            $discussion->user()->associate($user);
            $discussion->setFirstPost($post);
        }

        $discussion->refreshCommentCount();
        $discussion->refreshLastPost();
        $discussion->refreshParticipantCount();

        $discussion->save();

        if ($post->wasRecentlyCreated) {
            $post->raise(new Posted($post));
        }

        $this->events->dispatch(new EmailProcessed($this->message, $discussion, $post));

        $this->message->markAsSeen();
    }

    protected function importUser(Message $message): User
    {
        $from = $message->getFrom();

        /** @var UserRepository $repository */
        $repository = app(UserRepository::class);

        $user = $repository->findByEmail($from->getAddress());

        if (! $user) {
            $user = User::register(Str::slug($from->getAddress()), $from->getAddress(), Str::random());
            $user->save();
        }

        return $user;
    }
}
