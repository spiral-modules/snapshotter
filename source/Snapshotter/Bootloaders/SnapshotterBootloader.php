<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Debug\SnapshotInterface;
use Spiral\Snapshotter\DelegateSnapshot;

class SnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        SnapshotInterface::class  => DelegateSnapshot::class
    ];
}