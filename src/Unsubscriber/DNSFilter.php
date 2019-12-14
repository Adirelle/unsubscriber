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
 * Class DNSFilter.
 */
final class DNSFilter extends AbstractUnsubscriberDecorator
{
    /** @var string[] */
    private $seen = [];

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void
    {
        $link = $unsubscribeInfo->getLink();
        $host = parse_url($link, PHP_URL_HOST);
        $scheme = parse_url($link, PHP_URL_SCHEME);

        if (!$host) {
            $this->logger->info("ignoring malformed link: `{$link}`");

            return;
        }

        switch ($scheme) {
            case 'mailto':
                if (!$this->checkMailDomain($host)) {
                    $this->logger->info("ignoring email with no valid MX record: `{$host}`");

                    return;
                }

                break;
            case 'http':case 'https':
                if (!$this->checkIpAddress($host)) {
                    $this->logger->info("ignoring webserver with no valid A record: `{$host}`");

                    return;
                }

                break;
            default:
                break;
        }

        parent::unsubscribe($unsubscribeInfo);
    }

    /**
     * Check that the mail domain has MX record(s).
     */
    private function checkMailDomain(string $mail): bool
    {
        [, $domain] = explode('@', $mail, 2);
        if (!$domain) {
            $this->logger->info("malformed mail address: `{$mail}`");

            return false;
        }

        $this->logger->debug("Looking for MX records of `{$domain}`");

        return checkdnsrr($domain, 'MX');
    }

    /**
     * Check that the given host has a valid IP address.
     *
     * @return bool
     */
    private function checkIpAddress(string $host)
    {
        $this->logger->debug("Looking for A records of `{$host}`");
        $ipv4 = gethostbyname($host);

        return $ipv4 !== $host && '127.0.0.1' !== $ipv4;
    }
}
