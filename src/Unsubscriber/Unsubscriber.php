<?php

declare(strict_types=1);

namespace App\Unsubscriber;

use App\Mailbox\UnsubscribeInfo;

/**
 * Interface Unsubscriber.
 */
interface Unsubscriber
{
    /**
     * @param UnsubscribeInfo $unsubscribeInfo
     *
     * @return bool
     */
    public function supports(UnsubscribeInfo $unsubscribeInfo): bool;

    /**
     * @param UnsubscribeInfo $unsubscribeInfo
     *
     * @return bool
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void;
}
