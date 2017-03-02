<?php

namespace Spiral\Snapshotter\Helpers;

use Carbon\Carbon;

class Timestamps
{
    /**
     * @param mixed $timestamp
     * @param bool  $relative
     * @return string
     */
    public function getTime($timestamp, bool $relative = false): string
    {
        if (empty($relative)) {
            return $timestamp;
        }

        $carbon = new Carbon($timestamp);

        return $carbon->diffForHumans($carbon->now());
    }
}