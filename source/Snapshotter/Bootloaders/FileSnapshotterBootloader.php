<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Debug\SnapshotInterface;
use Spiral\Snapshotter\AbstractController;
use Spiral\Snapshotter\DelegateSnapshot;
use Spiral\Snapshotter\FileHandler;
use Spiral\Snapshotter\FileHandler\Controllers\SnapshotsController;
use Spiral\Snapshotter\HandlerInterface;

class FileSnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        HandlerInterface::class   => FileHandler::class,
        AbstractController::class => SnapshotsController::class,
        SnapshotInterface::class  => DelegateSnapshot::class
    ];
}