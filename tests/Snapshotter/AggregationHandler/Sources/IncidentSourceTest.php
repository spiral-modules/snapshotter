<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Sources;

use Spiral\Snapshotter\AggregationHandler;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Tests\BaseTest;

class IncidentSourceTest extends BaseTest
{
    /**
     * Test creation method
     */
    public function testCreateFromSnapshot()
    {
        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);
        $this->createIncident($source);

        $this->assertCount(1, $source->find());
    }

    /**
     * Test soft deletion method.
     */
    public function testDelete()
    {
        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);
        $incident = $this->createIncident($source);

        $source->delete($incident);

        /** @var IncidentRecord $incident */
        $incident = $source->findOne();

        $this->assertCount(1, $source->find());
        $this->assertEquals(true, $incident->status->isDeleted());
    }

    public function testFindStored()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);
        $snapshotRecord = $this->handleSnapshot($snapshot, true);

        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);

        $this->assertCount(1, $source->find());
        $this->assertCount(1, $source->findStored());
        $this->assertCount(1, $source->findStored($snapshotRecord));

        $snapshotRecord = $this->handleSnapshot($snapshot, true);

        $this->assertCount(2, $source->find());
        $this->assertCount(2, $source->findStored());
        $this->assertCount(2, $source->findStored($snapshotRecord));
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