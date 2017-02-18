<?php

namespace Spiral\Snapshotter\AggregationHandler\Database;

use Spiral\Models\Accessors\SqlTimestamp;
use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ORM\Record;
use Spiral\Support\Strings;

/**
 * Class Aggregation
 *
 * @property int          $id
 * @property SqlTimestamp time_created
 * @property string       $exception_source
 * @property string       $status
 * @property string       $exception_hash
 * @property string       $exception_teaser
 * @property string       $exception_classname
 * @property string       $exception_message
 * @property string       $exception_line
 * @property string       $exception_file
 * @property string       $exception_code
 * @package Spiral\Snapshotter\Database
 */
class IncidentRecord extends Record
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
        'id'                  => 'primary',
        'status'              => 'enum(stored,deleted,suppressed)',

        //exception fields
        'exception_hash'      => 'string',
        'exception_source'    => 'longText, nullable',
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
    const SECURED = [
        'exception_source', //should be passed via gzencode setter
    ];

    /**
     * {@inheritdoc}
     */
    const INDEXES = [
        [self::INDEX, 'exception_hash'],
    ];

    /**
     * {@inheritdoc}
     */
    const DEFAULTS = [
        'status' => 'stored'
    ];

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->status == 'deleted';
    }

    /**
     * @return bool
     */
    public function isSuppressed(): bool
    {
        return $this->status == 'suppressed';
    }

    /**
     * @return bool
     */
    public function isStored(): bool
    {
        return $this->status == 'stored';
    }

    /**
     *
     */
    public function suppress()
    {
        $this->status = 'suppressed';
        $this->exception_source = null;
    }

    /**
     *
     */
    public function setDeleted()
    {
        $this->status = 'deleted';
    }

    /**
     * @return null|string
     */
    public function getExceptionSource()
    {
        $source = $this->exception_source;
        if (empty($source)) {
            return null;
        }

        return gzdecode($this->exception_source);
    }

    public function setExceptionSource($source)
    {
        $this->exception_source = gzencode($source, 9);
    }

    /**
     * @return string
     */
    public function getExceptionCode()
    {
        return $this->exception_code;
    }

    /**
     * @return string
     */
    public function getExceptionHash()
    {
        return $this->exception_hash;
    }

    /**
     * @return string
     */
    public function getExceptionTeaser()
    {
        return $this->exception_teaser;
    }

    /**
     * @return string
     */
    public function getExceptionClass()
    {
        return $this->exception_classname;
    }

    /**
     * @return string
     */
    public function getExceptionMessage()
    {
        return $this->exception_message;
    }

    /**
     * @return string
     */
    public function getExceptionLine()
    {
        return $this->exception_line;
    }

    /**
     * @return string
     */
    public function getExceptionFile()
    {
        return $this->exception_file;
    }
}