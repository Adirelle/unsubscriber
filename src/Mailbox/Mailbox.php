<?php

declare(strict_types=1);

namespace App\Mailbox;

/**
 * Interface Mailbox.
 */
interface Mailbox
{
    /**
     * Enumerate the values of all Post-Unsubscribe headers of every mails in a given mailbox.
     *
     * @return iterable|UnsubscribeInfo[]
     */
    public function getListUnsubscribeHeaders(): iterable;
}
