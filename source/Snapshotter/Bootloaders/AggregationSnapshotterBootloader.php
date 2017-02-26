<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Snapshotter\AggregationHandler;
use Spiral\Snapshotter\AggregationHandler\Controllers\SnapshotsController;
use Spiral\Snapshotter\HandlerInterface;
use Spiral\Snapshotter\SnapshotterControllerInterface;

class AggregationSnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        HandlerInterface::class               => AggregationHandler::class,
        SnapshotterControllerInterface::class => SnapshotsController::class
    ];
}