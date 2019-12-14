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
 * Class CompositeUnsubcriber.
 */
final class CompositeUnsubcriber implements Unsubscriber
{
    /**
     * @var Unsubscriber[]
     */
    private $unsubscribers;

    /**
     * CompositeUnsubcriber constructor.
     *
     * @param Unsubscriber[] $Unsubscribers
     */
    public function __construct(array $unsubscribers)
    {
        $this->unsubscribers = $unsubscribers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UnsubscribeInfo $unsubscribeInfo): bool
    {
        foreach ($this->unsubscribers as $unsubscriber) {
            if ($unsubscriber->supports($unsubscribeInfo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void
    {
        foreach ($this->unsubscribers as $unsubscriber) {
            if ($unsubscriber->supports($unsubscribeInfo)) {
                $unsubscriber->unsubscribe($unsubscribeInfo);

                return;
            }
        }
    }
}
