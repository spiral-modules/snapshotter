<?php
/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 13.02.2016
 * Time: 17:07
 */

namespace Spiral\Snapshotter\Database;

use Carbon\Carbon;
use Spiral\Models\Accessors\SqlTimestamp;
use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ORM\Accessors\AtomicNumber;
use Spiral\ORM\Record;

/**
 * Class Aggregation
 *
 * @property int          $id
 * @property SqlTimestamp $last_occurred_time
 * @property bool         $suppression
 * @property string       $exception_hash
 * @property string       $exception_teaser
 * @property AtomicNumber $count_occurred
 * @property AtomicNumber $count_stored
 * @property AtomicNumber $count_suppressed
 * @property AtomicNumber $count_deleted
 * @package Spiral\Snapshotter\Database
 */
class Aggregation extends Record
{
    use TimestampsTrait;

    /**
     * {@inheritdoc}
     */
    protected $database = 'vault';

    /**
     * {@inheritdoc}
     */
    protected $schema = [
        //primary fields
        'id'                 => 'primary',
        'last_occurred_time' => 'datetime',
        'suppression'        => 'bool',

        //exception teaser
        'exception_hash'     => 'string',
        'exception_teaser'   => 'string',

        //counters
        'count_occurred'     => 'int',
        'count_stored'       => 'int',
        'count_suppressed'   => 'int',
        'count_deleted'      => 'int'
    ];

    /**
     * {@inheritdoc}
     */
    protected $indexes = [
        [self::UNIQUE, 'exception_hash'],
        [self::INDEX, 'exception_teaser']
    ];

    /**
     * {@inheritdoc}
     */
    protected $defaults = [
        'suppression' => false
    ];

    /**
     * {@inheritdoc}
     */
    protected $accessors = [
        'count_occurred'   => AtomicNumber::class,
        'count_suppressed' => AtomicNumber::class,
        'count_deleted'    => AtomicNumber::class,
        'count_stored'     => AtomicNumber::class
    ];

    /**
     * @return bool
     */
    public function isSuppressionEnabled()
    {
        return !empty($this->suppression);
    }

    /**
     * @param bool $relative
     * @return SqlTimestamp|string
     */
    public function whenFirst($relative = false)
    {
        return $this->when($this->time_created, $relative);
    }

    /**
     * @param bool $relative
     * @return SqlTimestamp|string
     */
    public function whenLast($relative = false)
    {
        return $this->when($this->last_occurred_time, $relative);
    }

    /**
     * @param SqlTimestamp $timestamp
     * @param bool         $relative
     * @return mixed
     */
    private function when($timestamp, $relative)
    {
        if (!empty($relative)) {
            return $timestamp->diffForHumans(Carbon::now());
        }

        return $timestamp;
    }

    /**
     * @param $suppression
     */
    public function setSuppression($suppression)
    {
        $this->suppression = $suppression;
    }
}
