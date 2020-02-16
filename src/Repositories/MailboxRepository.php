<?php

namespace FoF\Mailbox\Repositories;

use Flarum\Tags\Tag;
use FoF\Mailbox\Models\Mailbox;
use Illuminate\Support\Collection;

class MailboxRepository
{

    /**
     * @return Collection|Mailbox[]
     */
    public function ofTags(): Collection
    {
        return Tag::query()
            ->where('mailbox_enabled', true)
            ->get()
            ->map(function (Tag $tag) {
                return Mailbox::asTag($tag);
            });
    }
}
