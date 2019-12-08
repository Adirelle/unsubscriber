<?php

declare(strict_types=1);

namespace App;

use App\Mailbox\IMAPMailbox;
use App\Unsubscriber\CompositeUnsubcriber;
use App\Unsubscriber\WebUnsubscriber;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class UnsubscribeCommand.
 */
final class UnsubscribeCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UnsubscribeCommand constructor.
     *
     * @param null|LoggerInterface $logger
     */
    public function __construct(?LoggerInterface $logger)
    {
        $this->logger = $logger ?: new NullLogger();
        parent::__construct('unsubscribe');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addArgument('imapServer', InputArgument::REQUIRED, 'host of the IMAP server')
            ->addArgument('imapUser', InputArgument::REQUIRED, 'username to connect to the IMAP server')
            ->addArgument('imapPassword', InputArgument::REQUIRED, 'password to connect to server')
            ->addArgument('imapMailbox', InputArgument::OPTIONAL, 'name of the IMAP mailbox', 'INBOX')
            ->addArgument('imapPort', InputArgument::OPTIONAL, 'port of the IMAP server', 993)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->warning('bla');

        $mailbox = new IMAPMailbox(
            sprintf(
                '{%s:%s/imap/ssl}%s',
                (string) $input->getArgument('imapServer'),
                (int) $input->getArgument('imapPort'),
                (string) $input->getArgument('imapMailbox'),
            ),
            (string) $input->getArgument('imapUser'),
            (string) $input->getArgument('imapPassword'),
            $this->logger
        );

        $webUnsubscriber = new WebUnsubscriber(
            HttpClient::create(),
            $this->logger
        );
        $unsubscriber = new CompositeUnsubcriber([$webUnsubscriber]);

        $i = 10;
        foreach ($mailbox->getListUnsubscribeHeaders() as $unsubscribeHeader) {
            if ($unsubscriber->supports($unsubscribeHeader)) {
                $unsubscriber->unsubscribe($unsubscribeHeader);
            } else {
                $this->logger->info(sprintf('Skipping unsupported link: `%s`', $unsubscribeHeader->getLink()));
            }
            if (!$i--) {
                break;
            }
        }

        return 0;
    }
}
