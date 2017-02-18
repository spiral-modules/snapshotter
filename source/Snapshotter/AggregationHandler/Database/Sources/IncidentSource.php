<?php

namespace Spiral\Snapshotter\AggregationHandler\Database\Sources;

use Spiral\Debug\SnapshotInterface;
use Spiral\ORM\Entities\RecordSelector;
use Spiral\ORM\Entities\RecordSource;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;

class IncidentSource extends RecordSource
{
    const RECORD = IncidentRecord::class;

    /**
     * @param IncidentRecord $snapshot
     */
    public function delete(IncidentRecord $snapshot)
    {
        $snapshot->setDeleted();
        $snapshot->save();
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
     * @param string            $hash
     * @return IncidentRecord
     */
    public function createFromSnapshot(
        SnapshotInterface $snapshot,
        string $hash
    ): IncidentRecord
    {
        $exception = $snapshot->getException();
        $fields = [
            'exception_hash'      => $hash,
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