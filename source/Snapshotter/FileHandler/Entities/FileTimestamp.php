<?php

namespace Spiral\Snapshotter\FileHandler\Entities;

use Spiral\Models\Accessors\AbstractTimestamp;

class FileTimestamp extends AbstractTimestamp
{
    /**
     * @param mixed $value
     * @return int
     */
    public function fetchTimestamp($value): int
    {
        return $this->castTimestamp($value) ?? 0;
    }
}