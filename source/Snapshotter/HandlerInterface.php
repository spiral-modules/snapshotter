<?php

namespace Spiral\Snapshotter;

use Spiral\Debug\SnapshotInterface;

interface HandlerInterface
{
    /**
     * @param SnapshotInterface $snapshot
     */
    public function registerSnapshot(SnapshotInterface $snapshot);
}