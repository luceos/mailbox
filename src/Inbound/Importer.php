<?php

namespace FoF\Mailbox\Inbound;

use DateTimeZone;
use Ddeboer\Imap\Server;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Started;
use Flarum\Post\Event\Posted;
use Flarum\User\User;
use Flarum\User\UserRepository;
use FoF\Mailbox\Models\Mailbox;
use FoF\Mailbox\Models\Message;
use FoF\Mailbox\Repositories\DiscussionRepository;
use FoF\Mailbox\Repositories\MailboxRepository;
use FoF\Mailbox\Repositories\PostRepository;
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

    public function __construct(
        MailboxRepository $mailboxes,
        DiscussionRepository $discussions,
        PostRepository $posts,
        UserRepository $users
    ) {
        $this->mailboxes   = $mailboxes;
        $this->discussions = $discussions;
        $this->posts       = $posts;
        $this->users       = $users;
    }

    public function import(Mailbox $mailbox)
    {
        $connection = $mailbox->connection();

        foreach ($connection->getMailboxes() as $folder) {
            if (($folder->getAttributes() & \LATT_NOSELECT) || in_array(strtolower($folder->getName()), ['junk', 'sent', 'drafts', 'archive', 'trash'])) {
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
        $discussion = null;
        if ($message->reference) {
            foreach ($message->reference as $ref) {
                $discussion = $this->discussions->forThreadId($ref);

                if ($discussion) break;
            }
        } else {
            $discussion = $this->discussions->forThreadId($message->getId());
        }

        if (!$discussion->exists) {
            $discussion->title = $message->getSubject();

            $discussion->save();

            $discussion->raise(new Started($discussion));
        }

        $discussion->tags()->sync($mailbox->tag);

        $post          = $this->posts->forEmailId($message->getId(), $message->getNumber());
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
    }

    protected function importUser(Message $message): User
    {
        $from = $message->getFrom();

        $user = $this->users->findByEmail($from->getAddress());

        if (!$user) {
            $user = User::register($from->getMailbox(), $from->getAddress(), Str::random());
            $user->save();
        }

        return $user;
    }
}
