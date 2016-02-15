<?php
/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 13.02.2016
 * Time: 19:22
 */

namespace Spiral\Snapshotter\Models;

use Spiral\Snapshotter\Database\Sources\AggregationSource;

class Statistics
{
    /**
     * @var AggregationSource
     */
    protected $source;

    /**
     * Statistics constructor.
     *
     * @param AggregationSource $source
     */
    public function __construct(AggregationSource $source)
    {
        $this->source = $source;
    }
}