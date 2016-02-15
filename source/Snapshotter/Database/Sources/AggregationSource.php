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

class AggregationSource extends RecordSource
{
    const RECORD = Aggregation::class;

    /**
     * @param $exception_hash
     * @return null|Aggregation
     */
    public function findByHash($exception_hash)
    {
        return $this->find()->where(compact('exception_hash'))->findOne();
    }

    /**
     * @return \Spiral\ORM\Entities\RecordSelector
     */
    public function findWithSnapshots()
    {
        return $this->find()->where(['count_stored' => ['>=' => 1]]);
    }

    /**
     * @param Snapshot $snapshot
     * @return null|\Spiral\ORM\RecordEntity
     */
    public function findBySnapshot(Snapshot $snapshot)
    {
        return $this->findByPK($snapshot->aggregation_id);
    }

    /**
     * @return null|Aggregation
     */
    public function findLast()
    {
        return $this->findWithSnapshots()->orderBy('last_occurred_time', 'DESC')->findOne();
    }

    /**
     * @param Aggregation $entity
     * @param array       $errors
     * @return bool
     */
    public function save(Aggregation $entity, &$errors = null)
    {
        if (!$entity->save()) {
            $errors = $entity->getErrors();

            return false;
        }

        return true;
    }
}