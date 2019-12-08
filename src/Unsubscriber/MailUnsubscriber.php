<?php

declare(strict_types=1);

namespace App\Unsubscriber;

use App\Mailbox\UnsubscribeInfo;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Class MailUnsubscriber.
 */
final class MailUnsubscriber implements Unsubscriber
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MailUnsubscriber constructor.
     *
     * @param MailerInterface      $mailer
     * @param null|LoggerInterface $logger
     */
    public function __construct(MailerInterface $mailer, LoggerInterface $logger = null)
    {
        $this->mailer = $mailer;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UnsubscribeInfo $unsubscribeInfo): bool
    {
        return parse_url($unsubscribeInfo->getLink(), PHP_URL_SCHEME) === 'mailto';
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void
    {
        $parts = parse_url($unsubscribeInfo->getLink());
        $query = [];
        parse_str($parts['query'] ?? '', $query);

        $message = (new Email())
            ->subject($query['subject'] ?? 'unsubscribe')
            ->to($parts['path'])
            ->date(new \DateTime())
            ->from($unsubscribeInfo->getOriginalRecipient())
            ->returnPath($unsubscribeInfo->getOriginalRecipient())
            ->replyTo('no-reply@example.com')
            ->text('unsubscribe')
        ;
        if ($unsubscribeInfo->getMessageId()) {
            $message->getHeaders()->addIdHeader('In-Reply-To', $unsubscribeInfo->getMessageId());
        }
        $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-replied');

        $this->mailer->send($message);

        $this->logger->info(sprintf('unsubscribe mail sent to %s', $parts['path']));
    }
}
