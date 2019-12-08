<?php

declare(strict_types=1);

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
     *
     * @param int    $uid
     * @param string $subject
     * @param string $recipient
     * @param string $link
     * @param string $messageId
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
