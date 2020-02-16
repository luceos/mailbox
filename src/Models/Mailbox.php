<?php

namespace FoF\Mailbox\Models;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\Server;
use Flarum\Tags\Tag;
use Illuminate\Support\Fluent;
use Swift_Mailer;

/**
 * @property string   $sender
 * @property string   $host
 * @property int      $port
 * @property int      $smtp_ort
 * @property string   $encryption
 * @property string   $username
 * @property string   $password
 * @property Tag|null $tag
 */
class Mailbox extends Fluent
{
    public static function asTag(Tag $tag): self
    {
        return new self(
            [
                'sender'     => $tag->mailbox_sender,
                'host'       => $tag->mailbox_imap_host,
                'port'       => $tag->mailbox_imap_port,
                'smtp_port'  => $tag->mailbox_smtp_port,
                'encryption' => $tag->mailbox_imap_encryption,
                'username'   => $tag->mailbox_imap_username,
                'password'   => $tag->mailbox_imap_password,
                'tag'        => $tag
            ]
        );
    }

    public function connection(): ConnectionInterface
    {
        $flags = null;

        if ($this->encryption === 'ssl') {
            $flags = '/ssl';
        }

        $server = new Server($this->host, $this->port, $flags);

        return $server->authenticate($this->username, $this->password);
    }

    public function swift(): Swift_Mailer
    {
        $transport = (new \Swift_SmtpTransport($this->host, $this->smtp_port))
            ->setUsername($this->username)
            ->setPassword($this->password)
            ->setEncryption($this->encryption);

        return new Swift_Mailer($transport);
    }
}
