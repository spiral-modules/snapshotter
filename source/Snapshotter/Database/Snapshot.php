<?php
/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 10.02.2016
 * Time: 19:10
 */

namespace Spiral\Snapshotter\Database;

use Carbon\Carbon;
use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ORM\Record;
use Spiral\Support\Strings;

/**
 * Class Aggregation
 *
 * @property int    $id
 * @property string $filename
 * @property string $status
 * @property string $exception_hash
 * @property string $exception_teaser
 * @property string $exception_classname
 * @property string $exception_message
 * @property string $exception_line
 * @property string $exception_file
 * @property string $exception_code
 * @package Spiral\Snapshotter\Database
 */
class Snapshot extends Record
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
        'id'                  => 'primary',
        'filename'            => 'string',
        'status'              => 'enum(stored,deleted,suppressed)',

        //relations
//        'aggregation_id'      => 'bigint',
        'aggregation'         => [
            self::BELONGS_TO => Aggregation::class,
            self::INVERSE    => [self::HAS_MANY, 'snapshots']
        ],

        //exception fields
        'exception_hash'      => 'string',
        'exception_teaser'    => 'string',
        'exception_classname' => 'string',
        'exception_message'   => 'string',
        'exception_line'      => 'int',
        'exception_file'      => 'string',
        'exception_code'      => 'int'
    ];

    /**
     * {@inheritdoc}
     */
    protected $indexes = [
        [self::INDEX, 'exception_hash']
    ];

    /**
     * {@inheritdoc}
     */
    protected $defaults = [
        'status' => 'stored'
    ];

    /**
     * @param bool $relative
     * @return mixed
     */
    public function when($relative = false)
    {
        if (!empty($relative)) {
            return $this->time_created->diffForHumans(Carbon::now());
        }

        return $this->time_created;
    }

    /**
     * @return bool
     */
    public function deleted()
    {
        return $this->status == 'deleted';
    }

    /**
     * @return bool
     */
    public function suppressed()
    {
        return $this->status == 'suppressed';
    }

    /**
     * @return bool
     */
    public function stored()
    {
        return $this->status == 'stored';
    }

    /**
     *
     */
    public function setSuppressed()
    {
        $this->status = 'suppressed';
    }

    /**
     *
     */
    public function setDeleted()
    {
        $this->status = 'deleted';
    }

    /**
     * @param bool $format
     * @return int|string
     */
    public function filesize($format = false)
    {
        $filesize = filesize($this->filename);

        if ($format) {
            return Strings::bytes($filesize);
        }

        return $filesize;
    }
}