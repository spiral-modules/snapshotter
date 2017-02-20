<?php

namespace Spiral\Snapshotter\Helpers;

use Carbon\Carbon;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;

class Timestamps
{

    /**
     * @param mixed $timestamp
     * @param bool  $relative
     * @return string
     */
    private function getTime($timestamp, bool $relative = false): string
    {
        if (empty($relative)) {
            return $timestamp;
        }

        $carbon = new Carbon($timestamp);

        return $carbon->diffForHumans($carbon->now());
    }

    /**
     * @param SnapshotRecord $snapshot
     * @param bool           $relative
     * @return string
     */
    public function firstOccurred(SnapshotRecord $snapshot, bool $relative = false): string
    {
        return $this->getTime($snapshot->time_created, $relative);
    }

    /**
     * @param SnapshotRecord $snapshot
     * @param bool           $relative
     * @return string
     */
    public function lastOccurred(SnapshotRecord $snapshot, bool $relative = false): string
    {
        return $this->getTime($snapshot->last_incident->time_created, $relative);
    }

    /**
     * @param SnapshotRecord $snapshot
     * @param bool           $relative
     * @return string
     */
    public function timeOccurred(SnapshotRecord $snapshot, bool $relative = false): string
    {
        return $this->getTime($snapshot->time_created, $relative);
    }
}