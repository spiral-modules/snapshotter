<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Snapshotter\AggregationHandler;
use Spiral\Snapshotter\HandlerInterface;

class SnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        HandlerInterface::class => AggregationHandler::class
    ];
}