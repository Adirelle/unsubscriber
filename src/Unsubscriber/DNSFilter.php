<?php declare(strict_types=1);


namespace App\Unsubscriber;

use App\Mailbox\UnsubscribeInfo;

/**
 * Class DNSFilter
 *
 * @package App\Unsubscriber
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
            $this->logger->info("ignoring malformed link: `$link`");
            return;
        }

        switch ($scheme) {
            case 'mailto':
                if (!$this->checkMailDomain($host)) {
                    $this->logger->info("ignoring email with no valid MX record: `$host`");
                    return;
                }
                break;
            case 'http':case 'https':
                if (!$this->checkIpAddress($host)) {
                    $this->logger->info("ignoring webserver with no valid A record: `$host`");
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
     *
     * @param string $mail
     * @return bool
     */
    private function checkMailDomain(string $mail): bool
    {
        [, $domain] = explode('@', $mail, 2);
        if (!$domain) {
            $this->logger->info("malformed mail address: `$mail`");
            return false;
        }

        $this->logger->debug("Looking for MX records of `$domain`");
        return checkdnsrr($domain, 'MX');
    }

    /**
     * Check that the given host has a valid IP address.
     *
     * @param string $host
     * @return bool
     */
    private function checkIpAddress(string $host)
    {
        $this->logger->debug("Looking for A records of `$host`");
        $ipv4 = gethostbyname($host);
        return $ipv4 !== $host && $ipv4 !== '127.0.0.1';
    }
}
