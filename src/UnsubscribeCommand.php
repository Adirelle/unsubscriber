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

namespace App;

use App\Mailbox\IMAPMailbox;
use App\Unsubscriber\CompositeUnsubcriber;
use App\Unsubscriber\DedupFilter;
use App\Unsubscriber\DNSFilter;
use App\Unsubscriber\MailUnsubscriber;
use App\Unsubscriber\WebUnsubscriber;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

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
            ->addOption('config', null, InputArgument::REQUIRED, 'path of a configuration file')
            ->addOption('imap', null, InputArgument::REQUIRED, 'DSN of the IMAP server')
            ->addOption('imapUsername', null, InputArgument::REQUIRED, 'Username to conect to the IMAP server')
            ->addOption('imapPassword', null, InputArgument::REQUIRED, 'Password to conect to the IMAP server')
            ->addOption('smtp', null, InputArgument::REQUIRED, 'DSN of the SMTP server')
            ->addOption('limit', null, InputArgument::REQUIRED, 'maximum number of unsubscribption to process')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfig($input);

        $mailbox = new IMAPMailbox(
            $config['imap']['dsn'],
            $config['imap']['username'],
            $config['imap']['password'],
            $this->logger
        );

        $httpClient = HttpClient::create();
        if ($httpClient instanceof LoggerAwareInterface) {
            $httpClient->setLogger($this->logger);
        }

        $transport = Transport::fromDsn($config['smtp'], null, $httpClient, $this->logger);
        $mailer = new Mailer($transport);

        $webUnsubscriber = new WebUnsubscriber($httpClient, $this->logger);
        $mailUnsubscriber = new MailUnsubscriber($mailer, $this->logger);
        $unsubscriber = new DedupFilter(
            new DNSFilter(
                new CompositeUnsubcriber([$webUnsubscriber, $mailUnsubscriber]),
                $this->logger
            ),
            $this->logger
        );

        $limit = ((int) $input->getOption('limit')) ?: PHP_INT_MAX;

        foreach ($mailbox->getListUnsubscribeHeaders() as $unsubscribeHeader) {
            if ($unsubscriber->supports($unsubscribeHeader)) {
                $this->logger->debug(sprintf('Processing `%s`', $unsubscribeHeader->getLink()));
                $unsubscriber->unsubscribe($unsubscribeHeader);
                if (!--$limit) {
                    break;
                }
            } else {
                $this->logger->info(sprintf('Skipping unsupported link: `%s`', $unsubscribeHeader->getLink()));
            }
        }

        return 0;
    }

    private function getConfig(InputInterface $input): array
    {
        $config = [[
            'imap' => [
                'dsn' => false,
                'username' => false,
                'password' => false,
            ],
            'smtp' => false,
        ]];

        $configFilePath = $input->getOption('config');
        if (\is_string($configFilePath)) {
            if (!file_exists($configFilePath)) {
                throw new FileNotFoundException($configFilePath);
            }
            $config[] = $this->readConfigFile($configFilePath);
        } elseif (file_exists('unsubscriber.json')) {
            $config[] = $this->readConfigFile('unsubscriber.json');
        }

        if ($input->getOption('imap')) {
            $config[] = ['imap' => ['dsn' => (string) $input->getOption('imap')]];
        }

        if ($input->getOption('imapUsername')) {
            $config[] = ['imap' => ['username' => (string) $input->getOption('imapUsername')]];
        }

        if ($input->getOption('imapPassword')) {
            $config[] = ['imap' => ['password' => (string) $input->getOption('imapPassword')]];
        }

        if ($input->getOption('smtp')) {
            $config[] = ['smtp' => (string) $input->getOption('smtp')];
        }

        return array_replace_recursive(...$config);
    }

    private function readConfigFile(string $path): array
    {
        $this->logger->debug("reading configuration from `{$path}`");

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
