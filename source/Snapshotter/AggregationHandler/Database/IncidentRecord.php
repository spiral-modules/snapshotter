<?php

namespace Spiral\Snapshotter\AggregationHandler\Database;

use Spiral\Models\Accessors\SqlTimestamp;
use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ORM\Entities\Relations\HasOneRelation;
use Spiral\ORM\Record;
use Spiral\ORM\TransactionInterface;
use Spiral\Snapshotter\AggregationHandler\Database\Types\IncidentStatus;

/**
 * Class Aggregation
 *
 * @property int            $id
 * @property SqlTimestamp   time_created
 * @property string         $exception_source
 * @property IncidentStatus $status
 * @property string         $exception_hash
 * @property string         $exception_teaser
 * @property string         $exception_classname
 * @property string         $exception_message
 * @property string         $exception_line
 * @property string         $exception_file
 * @property string         $exception_code
 * @property HasOneRelation $parent_snapshot
 * @property HasOneRelation $snapshot
 */
class IncidentRecord extends Record
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
        'id'                  => 'primary',
        'status'              => IncidentStatus::class,

        //exception fields
        'exception_hash'      => 'string(128)',
        'exception_source'    => 'longBinary, nullable',
        'exception_teaser'    => 'string',
        'exception_classname' => 'string',
        'exception_message'   => 'string',
        'exception_line'      => 'int',
        'exception_file'      => 'string',
        'exception_code'      => 'int',
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
        [self::INDEX, 'exception_hash', 'status'],
    ];

    /**
     * Suppress incident. Set according status and clean source.
     */
    public function suppress()
    {
        $this->status->setSuppressed();
        $this->exception_source = null;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(TransactionInterface $transaction = null)
    {
        $this->status->setDeleted();
        $this->exception_source = null;
        $this->save();
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

    /**
     * @param string $source
     */
    public function setExceptionSource(string $source)
    {
        $this->exception_source = gzencode($source, 9);
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return $this->exception_code;
    }

    /**
     * @return string
     */
    public function getExceptionHash(): string
    {
        return $this->exception_hash;
    }

    /**
     * @return string
     */
    public function getExceptionTeaser(): string
    {
        return $this->exception_teaser;
    }

    /**
     * @return string
     */
    public function getExceptionClass(): string
    {
        return $this->exception_classname;
    }

    /**
     * @return string
     */
    public function getExceptionMessage(): string
    {
        return $this->exception_message;
    }

    /**
     * @return int
     */
    public function getExceptionLine(): int
    {
        return $this->exception_line;
    }

    /**
     * @return string
     */
    public function getExceptionFile(): string
    {
        return $this->exception_file;
    }
}