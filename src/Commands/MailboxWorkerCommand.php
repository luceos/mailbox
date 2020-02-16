<?php

namespace FoF\Mailbox\Commands;

use FoF\Mailbox\Inbound\Importer;
use FoF\Mailbox\Repositories\MailboxRepository;
use Illuminate\Console\Command;

class MailboxWorkerCommand extends Command
{
    protected $signature = 'mailbox:work';
    protected $description = 'Executes mailbox processing.';

    public function handle(MailboxRepository $mailboxes, Importer $importer)
    {
        foreach ($mailboxes->ofTags() as $mailbox) {
            $importer->import($mailbox);
        }
    }
}
