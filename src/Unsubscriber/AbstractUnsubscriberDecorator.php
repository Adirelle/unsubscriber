<?php declare(strict_types=1);


namespace App\Unsubscriber;


use App\Mailbox\UnsubscribeInfo;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractUnsubscriberDecorator implements Unsubscriber
{
    /**
     * @var Unsubscriber
     */
    private $inner;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DedupFilter constructor.
     *
     * @param Unsubscriber $inner
     */
    public function __construct(Unsubscriber $inner, LoggerInterface $logger = null)
    {
        $this->inner = $inner;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UnsubscribeInfo $unsubscribeInfo): bool
    {
        return $this->inner->supports($unsubscribeInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(UnsubscribeInfo $unsubscribeInfo): void
    {
        $this->inner->unsubscribe($unsubscribeInfo);
    }
}
