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
