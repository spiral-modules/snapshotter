<?php

namespace Spiral\Snapshotter\AggregationHandler\Database\Sources;

use Spiral\Debug\SnapshotInterface;
use Spiral\ORM\Entities\RecordSelector;
use Spiral\ORM\Entities\RecordSource;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;

class IncidentSource extends RecordSource
{
    const RECORD = IncidentRecord::class;

    /**
     * @param IncidentRecord $incident
     */
    public function delete(IncidentRecord $incident)
    {
        $incident->status->setDeleted();
        $incident->save();
    }

    /**
     * @param SnapshotRecord|null $snapshotRecord
     * @return RecordSelector
     */
    public function findStored(SnapshotRecord $snapshotRecord = null): RecordSelector
    {
        $where = ['status' => 'stored'];

        if (!empty($snapshotRecord)) {
            return $this->findBySnapshot($snapshotRecord)->where($where);
        }

        return $this->find($where);
    }

    /**
     * @param SnapshotRecord $snapshotRecord
     * @return RecordSelector
     */
    public function findBySnapshot(SnapshotRecord $snapshotRecord): RecordSelector
    {
        $where['exception_hash'] = $snapshotRecord->exception_hash;

        return $this->find($where);
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