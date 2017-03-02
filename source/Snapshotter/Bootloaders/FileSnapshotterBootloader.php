<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Debug;
use Spiral\Snapshotter\AbstractController;
use Spiral\Snapshotter\FileHandler;
use Spiral\Snapshotter\FileHandler\Controllers\SnapshotsController;
use Spiral\Snapshotter\HandlerInterface;

class FileSnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        HandlerInterface::class        => FileHandler::class,
        AbstractController::class      => SnapshotsController::class,
        Debug\SnapshotInterface::class => Debug\Snapshot::class
    ];
}