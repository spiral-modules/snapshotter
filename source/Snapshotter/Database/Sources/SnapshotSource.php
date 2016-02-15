<?php
/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 10.02.2016
 * Time: 22:27
 */

namespace Spiral\Snapshotter\Database\Sources;

use Spiral\ORM\Entities\RecordSource;
use Spiral\Snapshotter\Database\Aggregation;
use Spiral\Snapshotter\Database\Snapshot;

class SnapshotSource extends RecordSource
{
    const RECORD = Snapshot::class;

    /**
     * @param Snapshot $entity
     * @param array    $errors
     * @return bool
     */
    public function save(Snapshot $entity, &$errors = null)
    {
        if (!$entity->save()) {
            $errors = $entity->getErrors();

            return false;
        }

        return true;
    }

    /**
     * @param Snapshot $snapshot
     */
    public function delete(Snapshot $snapshot)
    {
        $snapshot->setDeleted();

        $this->save($snapshot);
    }

    /**
     * @param Aggregation|null $aggregation
     * @return \Spiral\ORM\Entities\RecordSelector
     */
    public function findStored(Aggregation $aggregation = null)
    {
        $where = [
            'status' => 'stored'
        ];

        if (!empty($aggregation)) {
            $where['aggregation_id'] = $aggregation->id;
        }

        return $this->find($where);
    }
}