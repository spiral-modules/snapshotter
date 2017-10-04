<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Snapshotter\AbstractController;
use Spiral\Snapshotter\AggregationHandler;
use Spiral\Snapshotter\AggregationHandler\Controllers\SnapshotsController;
use Spiral\Snapshotter\HandlerInterface;

class AggregationHandlerBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        HandlerInterface::class   => AggregationHandler::class,
        AbstractController::class => SnapshotsController::class
    ];
}