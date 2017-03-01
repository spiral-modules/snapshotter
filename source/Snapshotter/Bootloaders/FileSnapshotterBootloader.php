<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Debug;
use Spiral\Snapshotter\AbstractController;
use Spiral\Snapshotter\FileHandler\Controllers\SnapshotsController;

class FileSnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        AbstractController::class      => SnapshotsController::class,
        Debug\SnapshotInterface::class => Debug\Snapshot::class
    ];
}