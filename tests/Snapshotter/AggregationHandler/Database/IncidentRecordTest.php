<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Database;

use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler;
use Spiral\Tests\BaseTest;

class IncidentRecordTest extends BaseTest
{
    /**
     * Test suppression.
     */
    public function testSuppress()
    {
        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);
        $incident = $this->createIncident($source);

        $this->assertTrue($incident->status->isLast());
        $this->assertNotEmpty($incident->getExceptionSource());

        $incident->suppress();
        $this->assertTrue($incident->status->isSuppressed());
        $this->assertEmpty($incident->getExceptionSource());
    }

    /**
     * @param IncidentSource $source
     * @return IncidentRecord
     */
    private function createIncident(IncidentSource $source): IncidentRecord
    {
        $snapshot = $this->makeSnapshot('custom error', 123);
        $incident = $source->createFromSnapshot($snapshot);

        $incident->save();

        return $incident;
    }
}