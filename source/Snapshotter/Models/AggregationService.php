<?php
namespace Spiral\Snapshotter\Models;

use Interop\Container\ContainerInterface;
use Spiral\Core\Service;
use Spiral\Snapshotter\Database\Aggregation;
use Spiral\Snapshotter\Database\Snapshot;
use Spiral\Snapshotter\Database\Sources\AggregationSource;

/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 13.02.2016
 * Time: 18:00
 */
class AggregationService extends Service
{
    /**
     * @var AggregationSource
     */
    private $source = null;

    /**
     * AggregationService constructor.
     *
     * @param AggregationSource  $source
     * @param ContainerInterface $container
     */
    public function __construct(AggregationSource $source, ContainerInterface $container)
    {
        $this->source = $source;
        parent::__construct($container);
    }

    /**
     * @param $hash
     * @param $teaser
     * @return null|Aggregation
     */
    public function findOrCreateByHash($hash, $teaser)
    {
        /**
         * @var Aggregation $aggregation
         */
        $aggregation = $this->source->findByHash($hash);
        if (empty($aggregation)) {
            $aggregation = $this->source->create([
                'exception_hash'   => $hash,
                'exception_teaser' => $teaser,
            ]);

            $this->source->save($aggregation);
        }

        return $aggregation;
    }

    /**
     * @param Aggregation $aggregation
     * @param Snapshot    $snapshot
     * @param bool        $suppress
     */
    public function addSnapshot(Aggregation $aggregation, Snapshot $snapshot, $suppress = false)
    {
        $snapshot->aggregation_id = $aggregation->id;

        $aggregation->count_occurred->inc(1);
        $aggregation->last_occurred_time = time();

        if ($suppress) {
            $this->suppressSnapshot($aggregation);
            $snapshot->setSuppressed();
        } else {
            $aggregation->count_stored->inc(1);
        }
    }

    /**
     * @param Aggregation $aggregation
     * @param int         $inc
     */
    public function deleteSnapshots(Aggregation $aggregation, $inc)
    {
        $aggregation->count_deleted->inc($inc);
        $aggregation->count_stored->inc(-$inc);
    }

    /**
     * @param Aggregation $aggregation
     */
    public function suppressSnapshot(Aggregation $aggregation)
    {
        $aggregation->count_suppressed->inc(1);
    }

    /**
     * @return AggregationSource
     */
    public function getSource()
    {
        return $this->source;
    }
}