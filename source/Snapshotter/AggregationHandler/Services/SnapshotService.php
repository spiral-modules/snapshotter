<?php

namespace Spiral\Snapshotter\AggregationHandler\Services;

use Spiral\Core\Service;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;

class SnapshotService extends Service
{
    /** @var null|SnapshotSource */
    private $source = null;

    /**
     * AggregationService constructor.
     *
     * @param SnapshotSource $source
     */
    public function __construct(SnapshotSource $source)
    {
        $this->source = $source;
    }

    /**
     * @return SnapshotSource
     */
    public function getSource(): SnapshotSource
    {
        return $this->source;
    }

    /**
     * Select or create new Aggregation (you must save entity by yourself).
     *
     * @param string $hash
     * @return SnapshotRecord
     */
    public function getByHash(string $hash): SnapshotRecord
    {
        /** @var SnapshotRecord $aggregation */
        $aggregation = $this->source->findByHash($hash);
        if (empty($aggregation)) {
            $aggregation = $this->source->create();
            $aggregation->exception_hash = $hash;
        }

        return $aggregation;
    }

    /**
     * Count occurrence by intervals.
     *
     * @param SnapshotRecord $aggregation
     * @param IncidentSource $source
     * @return array
     */
    public function countOccurred(SnapshotRecord $aggregation, IncidentSource $source): array
    {
        $daily = $this->countIntervalOccurred($aggregation, $source, new \DateInterval('P1D'));
        $weekly = $this->countIntervalOccurred($aggregation, $source, new \DateInterval('P7D'));
        $monthly = $this->countIntervalOccurred($aggregation, $source, new \DateInterval('P1M'));
        $yearly = $this->countIntervalOccurred($aggregation, $source, new \DateInterval('P1Y'));

        return compact('daily', 'weekly', 'monthly', 'yearly');
    }

    /**
     * @param SnapshotRecord $aggregation
     * @param IncidentSource $source
     * @param \DateInterval  $interval
     * @return int
     */
    private function countIntervalOccurred(
        SnapshotRecord $aggregation,
        IncidentSource $source,
        \DateInterval $interval
    ): int
    {
        return $source->findBySnapshot($aggregation)->where(
            'time_created',
            '>=',
            (new \DateTime('now'))->sub($interval)
        )->count();
    }
}