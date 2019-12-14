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

final class MailUnsubscribeInfo implements UnsubscribeInfo
{
    /**
     * @var int
     */
    private $uid;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $recipient;

    /**
     * @var string
     */
    private $messageId;

    /**
     * MailUnsubscribeInfo constructor.
     */
    public function __construct(int $uid, string $subject, string $recipient, string $link, string $messageId)
    {
        $this->uid = $uid;
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->link = $link;
        $this->messageId = $messageId;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return sprintf('#%d: %s', $this->uid, $this->subject);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }
}
