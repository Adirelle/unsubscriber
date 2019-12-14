<?php

declare(strict_types=1);

namespace App\Unsubscriber;

use App\Mailbox\UnsubscribeInfo;

/**
 * Deduplicate links using their "host".
 */
final class DedupFilter extends AbstractUnsubscriberDecorator
{
    /** @var string[] */
    private $seen = [];

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void
    {
        $link = $unsubscribeInfo->getLink();
        $host = parse_url($link, PHP_URL_HOST);

        if (!$host || isset($this->seen[$host])) {
            return;
        }
        $this->seen[$host] = true;
        parent::unsubscribe($unsubscribeInfo);
    }
}
