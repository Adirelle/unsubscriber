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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractUnsubscriberDecorator implements Unsubscriber
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Unsubscriber
     */
    private $inner;

    /**
     * DedupFilter constructor.
     */
    public function __construct(Unsubscriber $inner, LoggerInterface $logger = null)
    {
        $this->inner = $inner;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UnsubscribeInfo $unsubscribeInfo): bool
    {
        return $this->inner->supports($unsubscribeInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void
    {
        $this->inner->unsubscribe($unsubscribeInfo);
    }
}
