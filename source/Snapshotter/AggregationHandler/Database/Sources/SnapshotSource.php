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
        return $this->findOne(['exception_hash'=> $hash]);
    }

    /**
     * todo refactor
     *
     * @return RecordSelector
     */
    public function findWithLast(): RecordSelector
    {
        return $this->find()->with('last_incident', ['alias' => 'last_incident']);
    }

    //для history load inload

//    /**
//     * todo refactor
//     *
//     * @param SnapshotRecord $snapshot
//     * @return null|SnapshotRecord
//     */
//    public function findBySnapshot(SnapshotRecord $snapshot)
//    {
//        return $snapshot->aggregation;
//    }

//    /**
//     * todo refactor
//     *
//     * @return null|SnapshotRecord
//     */
    public function findLast()
    {
        return $this->findWithLast()
            ->orderBy('last_incident.time_created', SelectQuery::SORT_DESC)
            ->findOne();
    }
}