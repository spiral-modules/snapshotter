<?php

namespace Spiral\Snapshotter;

use Spiral\Core\Service;
use Spiral\Debug\SnapshotInterface;
use Spiral\Snapshotter\HandlerInterface;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;

class AggregationHandler extends Service implements HandlerInterface
{
    /** @var IncidentSource */
    private $source;

    /** @var SnapshotService */
    private $aggregations;

    /**
     * Aggregation constructor.
     *
     * @param IncidentSource  $snapshots
     * @param SnapshotService $aggregations
     */
    public function __construct(IncidentSource $snapshots, SnapshotService $aggregations)
    {
        $this->source = $snapshots;
        $this->aggregations = $aggregations;
    }

    /**
     * Create snapshot aggregation and aggregated snapshot and tie them together.
     *
     * @param SnapshotInterface $snapshot
     */
    public function registerSnapshot(SnapshotInterface $snapshot)
    {
        $hash = self::makeHash($snapshot);

        /** @var IncidentRecord $snapshotEvent */
        $snapshotEvent = $this->source->createFromSnapshot($snapshot, $hash);

        /** @var SnapshotRecord $aggregation */
        $aggregation = $this->aggregations->getByHash($hash);
        $aggregation->pushIncident($snapshotEvent);
        $aggregation->save();
    }

    /**
     * Creates unique hash to allow aggregating snapshots.
     *
     * @param SnapshotInterface $snapshot
     * @return string
     */
    public static function makeHash(SnapshotInterface $snapshot): string
    {
        return hash('sha256', $snapshot->getMessage());
    }
}