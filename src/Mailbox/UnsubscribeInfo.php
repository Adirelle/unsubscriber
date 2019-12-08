<?php

declare(strict_types=1);

namespace App\Mailbox;

/**
 * Interface UnsubscribeInfo.
 */
interface UnsubscribeInfo
{
    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @return string
     */
    public function getLink(): string;

    /**
     * @return string
     */
    public function getOriginalRecipient(): string;

    /**
     * @return string
     */
    public function getDescription(): string;
}
