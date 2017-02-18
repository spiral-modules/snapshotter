<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Database;

use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler;
use Spiral\Tests\BaseTest;

class IncidentRecordTest extends BaseTest
{
    /**
     * Status setters and getters
     */
    public function testStatus()
    {
        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);
        $incident = $this->createIncident($source);

        //Stored by default
        $this->assertTrue($incident->isStored());

        $incident->status = 'deleted';
        $this->assertTrue($incident->isDeleted());

        $incident->status = 'stored';
        $this->assertTrue($incident->isStored());

        $incident->status = 'suppressed';
        $this->assertTrue($incident->isSuppressed());

        $incident->setDeleted();
        $this->assertEquals('deleted', $incident->status);
    }

    /**
     * Test suppression.
     */
    public function testSuppress()
    {
        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);
        $incident = $this->createIncident($source);

        $this->assertTrue($incident->isStored());
        $this->assertNotEmpty($incident->getExceptionSource());

        $incident->suppress();
        $this->assertTrue($incident->isSuppressed());
        $this->assertEmpty($incident->getExceptionSource());
    }

    /**
     * @param IncidentSource $source
     * @return IncidentRecord
     */
    private function createIncident(IncidentSource $source): IncidentRecord
    {
        $snapshot = $this->makeSnapshot('custom error', 123);
        $hash = AggregationHandler::makeHash($snapshot);
        $incident = $source->createFromSnapshot($snapshot, $hash);

        $incident->save();

        return $incident;
    }
}