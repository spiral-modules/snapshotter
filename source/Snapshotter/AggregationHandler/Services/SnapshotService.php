<?php

namespace Spiral\Snapshotter\AggregationHandler\Services;

use Spiral\Core\Service;
use Spiral\Debug\SnapshotInterface;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Database\Types\IncidentStatus;

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
        /** @var SnapshotRecord $snapshot */
        $snapshot = $this->source->findByHash($hash);
        if (empty($snapshot)) {
            $snapshot = $this->source->create();
            $snapshot->exception_hash = $hash;
        }

        return $snapshot;
    }

    /**
     * Count occurrence by intervals.
     *
     * @param SnapshotRecord $snapshot
     * @param IncidentSource $source
     * @return array
     */
    public function countOccurred(SnapshotRecord $snapshot, IncidentSource $source): array
    {
        $daily = $this->countIntervalOccurred($snapshot, $source, new \DateInterval('P1D'));
        $weekly = $this->countIntervalOccurred($snapshot, $source, new \DateInterval('P7D'));
        $monthly = $this->countIntervalOccurred($snapshot, $source, new \DateInterval('P1M'));
        $yearly = $this->countIntervalOccurred($snapshot, $source, new \DateInterval('P1Y'));

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

    /**
     * @param SnapshotRecord $snapshot
     */
    public function delete(SnapshotRecord $snapshot)
    {
        $snapshot->archiveLastIncident();
        $snapshot->forgetLastIncident();

        $incidents = $snapshot->getIncidentsHistory();

        /** @var IncidentRecord $incident */
        foreach ($incidents as $incident) {
            $incident->delete();
        }

        $snapshot->save();
    }
}