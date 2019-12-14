<?php

declare(strict_types=1);
/*
 * adirelle/unsubscriber - Scan your mailbox for mails with unsubscribe links and automatically unsubscribe
 * Copyright (C) 2019 Adirelle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Mailbox;

/**
 * Interface UnsubscribeInfo.
 */
interface UnsubscribeInfo
{
    public function __toString(): string;

    public function getLink(): string;

    public function getOriginalRecipient(): string;

    public function getMessageId(): string;

    public function getDescription(): string;
}
