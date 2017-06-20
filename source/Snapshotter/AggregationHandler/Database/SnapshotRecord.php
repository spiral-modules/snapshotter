<?php

namespace Spiral\Snapshotter\AggregationHandler\Database;

use Spiral\Models\Accessors\SqlTimestamp;
use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ORM\Entities\Relations\HasManyRelation;
use Spiral\ORM\Record;

/**
 * Class Aggregation
 *
 * @property SqlTimestamp   $time_created
 * @property bool           $suppression
 * @property string         $exception_hash
 * @property int            $count_occurred
 * @property IncidentRecord $last_incident
 *
 * @method HasManyRelation $incidents
 */
class SnapshotRecord extends Record
{
    use TimestampsTrait;

    /**
     * {@inheritdoc}
     */
    const DATABASE = 'snapshots';

    /**
     * {@inheritdoc}
     */
    const SCHEMA = [
        //primary fields
        'id'             => 'primary',
        'suppression'    => 'bool',

        //exception teaser
        'exception_hash' => 'string(128)',

        //counters
        'count_occurred' => 'int',

        //All occurred incidents before last one
        'incidents'      => [
            self::HAS_MANY => IncidentRecord::class
        ],

        //Last occurred incident
        'last_incident'  => [
            self::BELONGS_TO        => IncidentRecord::class,
            self::NULLABLE          => true,
            self::CREATE_CONSTRAINT => false
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
        $this->archiveLastIncident();

        $this->count_occurred++;
        $this->last_incident = $incident;
    }

    /**
     * Move last incident to history.
     */
    public function archiveLastIncident()
    {
        $lastIncident = $this->getLastIncident();
        if (!empty($lastIncident)) {
            if ($this->isSuppressionEnabled()) {
                //Remove exception trace before archiving
                $lastIncident->suppress();
            } else {
                $lastIncident->status->setStored();
            }

            //Move to history
            $this->incidents->add($lastIncident);
        }
    }

    /**
     * Forget last incident.
     */
    public function forgetLastIncident()
    {
        $this->last_incident = null;
    }
}