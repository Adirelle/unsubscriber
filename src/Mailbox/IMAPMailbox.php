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

use App\Exception\IMAPException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ZBateson\MailMimeParser\MailMimeParser;

final class IMAPMailbox implements Mailbox
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var MailMimeParser
     */
    private $messageParser;

    /**
     * IMAPMailbox constructor.
     */
    public function __construct(string $dsn, string $username = '', string $password = '', LoggerInterface $logger = null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->logger = $logger ?: new NullLogger();
        $this->messageParser = new MailMimeParser();
    }

    /**
     * {@inheritdoc}
     */
    public function getListUnsubscribeHeaders(): iterable
    {
        $conn = imap_open($this->dsn, $this->username, $this->password, 0, 3);
        if (false === $conn) {
            throw new IMAPException("could not connect to IMAP server {$this->dsn}: ".imap_last_error());
        }
        $this->logger->info('Connected to IMAP server '.$this->dsn);

        try {
            foreach ($this->getMailUIDs($conn) as $mailUID) {
                $this->logger->debug(sprintf('Reading mail #%d', $mailUID));
                yield from $this->extractMailData($conn, $mailUID);
                imap_setflag_full($conn, (string) $mailUID, 'Unsubscribed', ST_UID);
            }
        } finally {
            $this->logger->info('Closing IMAP connection');
            imap_close($conn);
        }
    }

    /**
     * @param resource $conn
     */
    private function getMailUIDs($conn): iterable
    {
        $messages = imap_search($conn, 'UNDELETED UNKEYWORD "Unsubscribed"', SE_UID);
        $this->logger->notice(sprintf('Found %d mails', \count($messages)));

        return $messages ?: [];
    }

    /**
     * @param $conn
     */
    private function extractMailData($conn, int $mailUID): iterable
    {
        $allHeaders = imap_fetchheader($conn, $mailUID, FT_UID);

        $message = $this->messageParser->parse($allHeaders);

        $subject = $message->getHeaderValue('Subject');
        $recipient = $message->getHeaderValue('To');
        $messageId = $message->getHeaderValue('MessageId');

        foreach ($message->getAllHeadersByName('List-Unsubscribe') as $header) {
            $value = $header->getValue();
            if (!$value) {
                continue;
            }
            if (preg_match_all('/<((https?|mailto):[^>]+)>/', $value, $links)) {
                foreach ($links[1] as $link) {
                    yield new MailUnsubscribeInfo($mailUID, $subject, $recipient, $link, $messageId);
                }
            }
        }
    }
}
