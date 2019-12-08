<?php

declare(strict_types=1);

namespace App\Unsubscriber;

use App\Mailbox\UnsubscribeInfo;

final class DedupperUnsubscriber implements Unsubscriber
{
    /**
     * @var Unsubscriber
     */
    private $inner;

    /** @var string[] */
    private $seen = [];

    /**
     * DedupperUnsubscriber constructor.
     *
     * @param Unsubscriber $inner
     */
    public function __construct(Unsubscriber $inner)
    {
        $this->inner = $inner;
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
        if (isset($this->seen[$unsubscribeInfo->getLink()])) {
            return;
        }
        $this->seen[$unsubscribeInfo->getLink()] = true;
        $this->inner->unsubscribe($unsubscribeInfo);
    }
}
