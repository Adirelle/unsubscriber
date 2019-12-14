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

namespace App\Unsubscriber;

use App\Mailbox\UnsubscribeInfo;

/**
 * Interface Unsubscriber.
 */
interface Unsubscriber
{
    public function supports(UnsubscribeInfo $unsubscribeInfo): bool;

    /**
     * @return bool
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void;
}
