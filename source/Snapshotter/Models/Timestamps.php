<?php

namespace Spiral\Snapshotter\Models;

use Carbon\Carbon;
use Spiral\Snapshotter\Database\SnapshotAggregation;
use Spiral\Snapshotter\Database\AggregatedSnapshot;

class Timestamps
{
    /** @var Carbon */
    private $carbon;

    /**
     * Timestamps constructor.
     *
     * @param Carbon $carbon
     */
    public function __construct(Carbon $carbon)
    {
        $this->carbon = $carbon;
    }

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

        $this->carbon->setTimestamp($timestamp);

        return $this->carbon->diffForHumans($this->carbon->now());
    }

    /**
     * @param SnapshotAggregation $aggregation
     * @param bool                $relative
     * @return string
     */
    public function firstOccurred(SnapshotAggregation $aggregation, bool $relative = false): string
    {
        return $this->getTime($aggregation->time_created, $relative);
    }

    /**
     * @param SnapshotAggregation $aggregation
     * @param bool                $relative
     * @return string
     */
    public function lastOccurred(SnapshotAggregation $aggregation, bool $relative = false): string
    {
        return $this->getTime($aggregation->last_occurred_time, $relative);
    }

    /**
     * @param AggregatedSnapshot $snapshot
     * @param bool               $relative
     * @return string
     */
    public function timeOccurred(AggregatedSnapshot $snapshot, bool $relative = false): string
    {
        return $this->getTime($snapshot->time_created, $relative);
    }
}