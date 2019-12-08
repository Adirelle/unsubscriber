<?php

declare(strict_types=1);

namespace App\Unsubscriber;

use App\Mailbox\UnsubscribeInfo;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Unsubscribe using an HTTP get request.
 */
final class WebUnsubscriber implements Unsubscriber
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * WebUnsubscriber constructor.
     *
     * @param HttpClientInterface  $httpClient
     * @param null|LoggerInterface $logger
     */
    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger = null)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UnsubscribeInfo $unsubscribeInfo): bool
    {
        return 0 === strpos($unsubscribeInfo->getLink(), 'http');
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void
    {
        $this->logger->debug(sprintf('GET %s', $unsubscribeInfo->getLink()));

        try {
            $response = $this->httpClient->request('GET', $unsubscribeInfo->getLink());

            $this->logger->info(sprintf('`%s`: %d', $unsubscribeInfo, $response->getStatusCode()));
            $this->logger->debug(sprintf('GET %s: `%s`', $unsubscribeInfo->getLink(), trim($response->getContent(false))));
        } catch (TransportException $ex) {
            $this->logger->warning(sprintf('error with `%s`: %s', $unsubscribeInfo, $ex->getMessage()));
        }
    }
}
