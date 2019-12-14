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
            return;
        }

        switch ($scheme) {
            case 'mailto':
                if (!$this->checkMXRecord($host)) {
                    $this->logger->info("ignoring email with no valid MX record: `$host`");
                    return;
                }
                break;
            case 'http':case 'https':
                if (!$this->checkARecord($host)) {
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
     * Check that the mail domain has a MX record.
     *
     * @param string $mail
     * @return bool
     */
    private function checkMXRecord(string $mail): bool
    {
        [, $domain] = explode('@', $mail, 2);
        if (!$domain) {
            $this->logger->info("malformed mail address: `$mail`");
            return false;
        }

        $mxRecords = dns_get_record($domain, DNS_MX);
        foreach ($mxRecords as $mxRecord) {
            if ($mxRecord['type'] === 'MX') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check that the given host has an IP address, which is different from "localhost".
     *
     * @param string $host
     * @return bool
     */
    private function checkARecord(string $host)
    {
        $mxRecords = dns_get_record($host, DNS_A | DNS_AAAA);
        foreach ($mxRecords as $mxRecord) {
            if ($mxRecord['type'] === 'A' && $mxRecord['ip'] !== '127.0.0.1') {
                return true;
            }
            if ($mxRecord['type'] === 'AAAA' && $mxRecord['ip'] !== '::1') {
                return true;
            }
        }

        return false;
    }
}
