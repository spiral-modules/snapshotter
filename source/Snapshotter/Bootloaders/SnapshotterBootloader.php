<?php

namespace Spiral\Snapshotter\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Debug\SnapshotInterface;

class SnapshotterBootloader extends Bootloader
{
    /**
     * {@inheritdoc}
     */
    const BINDINGS = [
        SnapshotInterface::class  => DelegateSnapshot::class
    ];
}