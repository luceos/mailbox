<?php

namespace FoF\Mailbox\Inbound;

use DateTimeZone;
use Flarum\Discussion\Event\Started;
use Flarum\Post\Event\Posted;
use Flarum\User\User;
use Flarum\User\UserRepository;
use FoF\Mailbox\Events\EmailProcessed;
use FoF\Mailbox\Events\EmailReceived;
use FoF\Mailbox\Events\IgnoredFolders;
use FoF\Mailbox\Models\Mailbox;
use FoF\Mailbox\Models\Message;
use FoF\Mailbox\Repositories\DiscussionRepository;
use FoF\Mailbox\Repositories\MailboxRepository;
use FoF\Mailbox\Repositories\PostRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

class Importer
{
    /**
     * @var MailboxRepository
     */
    private $mailboxes;
    /**
     * @var DiscussionRepository
     */
    private $discussions;
    /**
     * @var PostRepository
     */
    private $posts;
    /**
     * @var UserRepository
     */
    private $users;
    /**
     * @var Dispatcher
     */
    private $events;

    public function __construct(
        MailboxRepository $mailboxes,
        DiscussionRepository $discussions,
        PostRepository $posts,
        UserRepository $users,
        Dispatcher $events
    ) {
        $this->mailboxes   = $mailboxes;
        $this->discussions = $discussions;
        $this->posts       = $posts;
        $this->users       = $users;
        $this->events = $events;
    }

    public function import(Mailbox $mailbox)
    {
        $connection = $mailbox->connection();

        $ignoredFolders = ['junk', 'sent', 'drafts', 'archive', 'trash', 'spam'];

        $this->events->dispatch(new IgnoredFolders($ignoredFolders));

        foreach ($connection->getMailboxes() as $folder) {
            if (($folder->getAttributes() & \LATT_NOSELECT) || in_array(strtolower($folder->getName()), $ignoredFolders)) {
                continue;
            }

            if ($folder->count() > 0) {
                foreach ($folder->getMessages() as $imap) {
                    $message = new Message($imap);

                    if ($message->validForImport()) {
                        $user = $this->importUser($message);
                        $this->importMessage($mailbox, $message, $user);
                    }
                }
            }
        }
    }

    protected function importMessage(Mailbox $mailbox, Message $message, User $user)
    {
        $discussion = $post = null;

        if (empty($message->getBodyText())) return;

        if ($message->reference) {
            foreach ($message->reference as $ref) {
                $discussion = $this->discussions->forThreadId($ref);

                if ($discussion) break;
            }
        } else {
            $discussion = $this->discussions->forThreadId($message->getId());
        }

        $this->events->dispatch(new EmailReceived($message, $discussion, $post));

        if (!$discussion->exists) {
            $discussion->title = $message->getSubject();

            $discussion->save();

            $discussion->raise(new Started($discussion));
        }

        $discussion->tags()->sync($mailbox->tag);

        if (! $post) {
            $post = $this->posts->forEmailId($message->getId(), $message->getNumber());
        }
        $post->content = $message->getBodyText();
        $post->discussion()->associate($discussion);
        $post->user()->associate($user);
        $post->created_at = $message->getDate()->setTimezone(new DateTimeZone('UTC'));;
        $post->save();

        if ($message->isFirst || $discussion->first_post_id === null) {
            $discussion->created_at = $message->getDate()->setTimezone(new DateTimeZone('UTC'));
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

        $this->events->dispatch(new EmailProcessed($message, $discussion, $post));
    }

    protected function importUser(Message $message): User
    {
        $from = $message->getFrom();

        $user = $this->users->findByEmail($from->getAddress());

        if (!$user) {
            $user = User::register(Str::slug($from->getAddress()), $from->getAddress(), Str::random());
            $user->save();
        }

        return $user;
    }
}
