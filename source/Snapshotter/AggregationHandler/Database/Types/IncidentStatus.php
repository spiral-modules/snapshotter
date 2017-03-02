<?php

namespace Spiral\Snapshotter\AggregationHandler\Database\Types;

use Spiral\ORM\Columns\EnumColumn;

class IncidentStatus extends EnumColumn
{
    /**
     * Statuses.
     */
    const STORED     = 'stored';
    const DELETED    = 'deleted';
    const SUPPRESSED = 'suppressed';
    const LAST       = 'last';

    /**
     * Values.
     */
    const VALUES  = [self::STORED, self::DELETED, self::SUPPRESSED, self::LAST];

    /**
     * Default values.
     */
    const DEFAULT = self::LAST;

    /**
     * @return bool
     */
    public function isLast(): bool
    {
        return $this->packValue() == self::LAST;
    }

    /**
     * @return bool
     */
    public function isStored(): bool
    {
        return $this->packValue() == self::STORED;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->packValue() == self::DELETED;
    }

    /**
     * @return bool
     */
    public function isSuppressed(): bool
    {
        return $this->packValue() == self::SUPPRESSED;
    }

    public function setStored()
    {
        $this->setValue(self::STORED);
    }

    public function setDeleted()
    {
        $this->setValue(self::DELETED);
    }

    public function setSuppressed()
    {
        $this->setValue(self::SUPPRESSED);
    }
}