<?php

namespace Spiral\Snapshotter\AggregationHandler\Database\Sources;

use Spiral\Database\Injections\Parameter;
use Spiral\Debug\SnapshotInterface;
use Spiral\ORM\Entities\RecordSelector;
use Spiral\ORM\Entities\RecordSource;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Types\IncidentStatus;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;

class IncidentSource extends RecordSource
{
    const RECORD = IncidentRecord::class;

    /**
     * @param SnapshotRecord|null $snapshot
     * @return RecordSelector
     */
    public function findBySnapshotWithSource(SnapshotRecord $snapshot = null): RecordSelector
    {
        $where = [
            'status' => [
                'IN' => new Parameter([IncidentStatus::LAST, IncidentStatus::STORED])
            ]
        ];

        if (!empty($snapshot)) {
            return $this->findBySnapshot($snapshot)->where($where);
        }

        return $this->find($where);
    }

    /**
     * @param SnapshotRecord $snapshotRecord
     * @return RecordSelector
     */
    public function findBySnapshot(SnapshotRecord $snapshotRecord): RecordSelector
    {
        $where = [
            'exception_hash' => $snapshotRecord->exception_hash
        ];

        return $this->find($where);
    }

    /**
     * @param SnapshotRecord $snapshotRecord
     * @return RecordSelector
     */
    public function findSnapshotHistory(SnapshotRecord $snapshotRecord): RecordSelector
    {
        $where = [
            'exception_hash' => $snapshotRecord->exception_hash,
            'status'         => [
                'IN' => new Parameter([
                    IncidentStatus::STORED,
                    IncidentStatus::SUPPRESSED
                ])
            ]
        ];

        return $this->find($where);
    }

    /**
     * @param SnapshotRecord $snapshotRecord
     * @param string|int     $id
     * @param array          $load
     * @return null|IncidentRecord
     */
    public function findStoredBySnapshotByPK(SnapshotRecord $snapshotRecord, $id, array $load = [])
    {
        /** @var IncidentRecord $incident */
        $incident = $this->findByPK($id, $load);
        if (
            empty($incident) ||
            !$incident->status->isStored() ||
            $incident->getExceptionHash() !== $snapshotRecord->exception_hash
        ) {
            return null;
        }

        return $incident;
    }

    /**
     * @param SnapshotRecord $snapshotRecord
     * @param string|int     $id
     * @param array          $load
     * @return null|IncidentRecord
     */
    public function findBySnapshotByPK(SnapshotRecord $snapshotRecord, $id, array $load = [])
    {
        /** @var IncidentRecord $incident */
        $incident = $this->findByPK($id, $load);
        if (
            empty($incident) ||
            $incident->getExceptionHash() !== $snapshotRecord->exception_hash
        ) {
            return null;
        }

        return $incident;
    }

    /**
     * @param SnapshotInterface $snapshot
     * @return IncidentRecord
     */
    public function createFromSnapshot(SnapshotInterface $snapshot): IncidentRecord
    {
        $exception = $snapshot->getException();
        $fields = [
            'exception_hash'      => SnapshotService::makeHash($snapshot),
            'exception_teaser'    => $snapshot->getMessage(),
            'exception_classname' => get_class($exception),
            'exception_message'   => $exception->getMessage(),
            'exception_line'      => $exception->getLine(),
            'exception_file'      => $exception->getFile(),
            'exception_code'      => $exception->getCode(),
        ];

        /** @var IncidentRecord $incident */
        $incident = $this->create($fields);
        $incident->setExceptionSource($snapshot->render());

        return $incident;
    }
}