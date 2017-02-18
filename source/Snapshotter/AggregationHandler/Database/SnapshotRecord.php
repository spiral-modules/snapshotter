<?php

namespace Spiral\Snapshotter\AggregationHandler\Database;

use Spiral\Models\Accessors\SqlTimestamp;
use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ORM\Entities\Relations\HasManyRelation;
use Spiral\ORM\Record;

/**
 * Class Aggregation
 *
 * @property int          $id
 * @property SqlTimestamp $time_created
 * @property SqlTimestamp $last_occurred_time
 * @property bool         $suppression
 * @property string       $exception_hash
 * @property int          $count_occurred_total
 * @property int          $count_occurred_daily
 * @property int          $count_occurred_weekly
 * @property int          $count_occurred_monthly
 * @property int          $count_occurred_yearly
 * @method HasManyRelation $snapshots
 * @package Spiral\Snapshotter\Database
 */
class SnapshotRecord extends Record
{
    use TimestampsTrait;

    /**
     * {@inheritdoc}
     */
    //const DATABASE = 'snapshots';

    /**
     * {@inheritdoc}
     */
    const SCHEMA = [
        //primary fields
        'id'             => 'primary',
        'suppression'    => 'bool',

        //exception teaser
        'exception_hash' => 'string',

        //counters
        'count_occurred' => 'int',

        //All occurred incidents before last one
        'incidents'      => [
            self::HAS_MANY            => IncidentRecord::class
        ],

        //Last occurred incident
        'last_incident'  => [
            self::BELONGS_TO          => IncidentRecord::class,
            self::NULLABLE            => true
        ]
    ];

    /**
     * {@inheritdoc}
     */
    const INDEXES = [
        [self::UNIQUE, 'exception_hash'],
    ];

    /**
     * {@inheritdoc}
     */
    const DEFAULTS = [
        'suppression' => false
    ];

    /**
     * @return bool
     */
    public function isSuppressionEnabled(): bool
    {
        return !empty($this->suppression);
    }

    /**
     * @param bool $suppression
     */
    public function setSuppression(bool $suppression)
    {
        $this->suppression = $suppression;
    }

    /**
     * @return HasManyRelation
     */
    public function getIncidentsHistory(): HasManyRelation
    {
        return $this->incidents;
    }

    /**
     * @return IncidentRecord|null
     */
    public function getLastIncident()
    {
        return $this->last_incident;
    }

    /**
     * @param IncidentRecord $incident
     */
    public function pushIncident(IncidentRecord $incident)
    {
        $lastIncident = $this->getLastIncident();
        if (!empty($lastIncident)) {

            if ($this->isSuppressionEnabled()) {
                //Remove exception trace before archiving
                $lastIncident->suppress();
            }

            //Move to history
            $this->incidents->add($lastIncident);
        }

        $this->count_occurred++;
        $this->last_incident = $incident;
    }
}