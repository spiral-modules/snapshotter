<?php

namespace Spiral\Snapshotter;

use Spiral\Debug\SnapshotInterface;

interface HandlerInterface
{
    /**
     * @param SnapshotInterface $snapshotInterface
     */
    public function registerSnapshot(SnapshotInterface $snapshotInterface);
}