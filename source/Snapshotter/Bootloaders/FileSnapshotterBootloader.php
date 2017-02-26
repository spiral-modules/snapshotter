<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Snapshotter\FileHandler\Controllers\SnapshotsController;
use Spiral\Snapshotter\SnapshotterControllerInterface;

class FileSnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        SnapshotterControllerInterface::class => SnapshotsController::class
    ];
}