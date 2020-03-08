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
use Illuminate\Contracts\Queue\Queue;
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
    /**
     * @var Queue
     */
    private $queue;

    public function __construct(
        MailboxRepository $mailboxes,
        DiscussionRepository $discussions,
        PostRepository $posts,
        UserRepository $users,
        Dispatcher $events,
        Queue $queue
    ) {
        $this->mailboxes   = $mailboxes;
        $this->discussions = $discussions;
        $this->posts       = $posts;
        $this->users       = $users;
        $this->events = $events;
        $this->queue = $queue;
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
                        $this->queue->push(new ImportJob($mailbox, $message));
                    }
                }
            }
        }
    }
}
