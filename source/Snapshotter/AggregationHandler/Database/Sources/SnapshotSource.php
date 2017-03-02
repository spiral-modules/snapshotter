<?php

namespace Spiral\Snapshotter\AggregationHandler\Database\Sources;

use Spiral\Database\Builders\SelectQuery;
use Spiral\ORM\Entities\RecordSelector;
use Spiral\ORM\Entities\RecordSource;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;

class SnapshotSource extends RecordSource
{
    const RECORD = SnapshotRecord::class;

    /**
     * @param string $hash
     * @return null|SnapshotRecord
     */
    public function findByHash(string $hash)
    {
        return $this->findOne(['exception_hash' => $hash]);
    }

    /**
     * @return RecordSelector
     */
    public function findWithLast(): RecordSelector
    {
        return $this->find()->with('last_incident', ['alias' => 'last_incident']);
    }

    /**
     * @return null|SnapshotRecord
     */
    public function findLast()
    {
        return $this->findWithLast()
            ->orderBy('last_incident.time_created', SelectQuery::SORT_DESC)
            ->findOne();
    }

    /**
     * Snapshot with last incident (should not be archived in the history).
     *
     * @param string|int $id
     * @param array      $load
     * @return null|SnapshotRecord
     */
    public function findWithLastByPK($id, array $load = [])
    {
        /** @var SnapshotRecord|null $snapshot */
        $snapshot = $this->findByPK($id, $load);
        if (empty($snapshot) || empty($snapshot->getLastIncident())) {
            return null;
        }

        return $snapshot;
    }
}