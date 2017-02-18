<?php

namespace Spiral\Snapshotter;

use Psr\Log\LoggerInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Debug\QuickSnapshot;
use Spiral\Debug\Snapshot;

class DelegateSnapshot extends QuickSnapshot
{
    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var Snapshot
     */
    private $snapshot;

    /**
     * DelegateSnapshot constructor.
     *
     * @param \Throwable       $exception
     * @param LoggerInterface  $logger
     * @param FactoryInterface $factory
     * @param HandlerInterface $handler
     */
    public function __construct(
        \Throwable $exception,
        LoggerInterface $logger,
        FactoryInterface $factory,
        HandlerInterface $handler
    ) {
        parent::__construct($exception, $logger);

        $this->snapshot = $factory->make(Snapshot::class, compact('exception'));
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function report()
    {
        //Report error into error log
        parent::report();

        //Store snapshot information in database
        $this->handler->registerSnapshot($this->snapshot);
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        return $this->snapshot->render();
    }
}