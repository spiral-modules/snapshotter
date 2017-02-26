<?php

namespace Spiral\Snapshotter;

use Spiral\Core\Service;
use Spiral\Debug\SnapshotInterface;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;

class AggregationHandler extends Service implements HandlerInterface
{
    /** @var IncidentSource */
    private $source;

    /** @var SnapshotService */
    private $service;

    /**
     * Aggregation constructor.
     *
     * @param IncidentSource  $snapshots
     * @param SnapshotService $service
     */
    public function __construct(IncidentSource $snapshots, SnapshotService $service)
    {
        $this->source = $snapshots;
        $this->service = $service;
    }

    /**
     * Create snapshot aggregation and aggregated snapshot and tie them together.
     *
     * @param SnapshotInterface $snapshotInterface
     */
    public function registerSnapshot(SnapshotInterface $snapshotInterface)
    {
        /** @var IncidentRecord $incident */
        $incident = $this->source->createFromSnapshot($snapshotInterface);

        /** @var SnapshotRecord $snapshot */
        $snapshot = $this->service->getByHash($this->service->makeHash($snapshotInterface));
        $snapshot->pushIncident($incident);
        $snapshot->save();
    }
}