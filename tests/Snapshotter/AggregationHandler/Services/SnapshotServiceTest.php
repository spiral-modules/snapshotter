<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Services;

use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\AggregationHandler\Database\Types\IncidentStatus;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;
use Spiral\Tests\BaseTest;

class SnapshotServiceTest extends BaseTest
{
    /**
     * Source getter.
     */
    public function testSource()
    {
        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);
        $source = $service->getSource();
        $this->assertInstanceOf(SnapshotSource::class, $source);
    }

    /**
     * Test snapshot aggregation created by hash. Created only once.
     */
    public function testGetByHash()
    {
        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);

        $this->assertCount(0, $this->orm->source(SnapshotRecord::class)->find());

        $hash = 'some hash';
        $snapshot = $service->getByHash($hash);
        $snapshot->save();

        $this->assertCount(1, $this->orm->source(SnapshotRecord::class)->find());

        $hash2 = 'some hash2';
        $snapshot = $service->getByHash($hash2);
        $snapshot->save();

        $this->assertCount(2, $this->orm->source(SnapshotRecord::class)->find());
    }

    /**
     * Test snapshot aggregation created by hash. Created only once.
     */
    public function testCountOccurred()
    {
        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);
        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);

        $snapshot = $this->makeSnapshot('custom error', 777);

        //Create 2 incidents
        $snapshotRecord = $this->handleSnapshot($snapshot, true);
        $counters = $service->countOccurred($snapshotRecord, $source);

        $this->assertEquals(1, $counters['daily']);
        $this->assertEquals(1, $counters['weekly']);
        $this->assertEquals(1, $counters['monthly']);
        $this->assertEquals(1, $counters['yearly']);

        $this->handleSnapshot($snapshot, true);
        $counters = $service->countOccurred($snapshotRecord, $source);

        $this->assertEquals(2, $counters['daily']);
        $this->assertEquals(2, $counters['weekly']);
        $this->assertEquals(2, $counters['monthly']);
        $this->assertEquals(2, $counters['yearly']);

        /** @var IncidentRecord $incident */
        $incident = $snapshotRecord->getIncidentsHistory()->getIterator()->current();

        //One of them occurred not in this day
        $incident->time_created = (new \DateTime('now'))->sub(new \DateInterval('P2D'));
        $incident->save();

        $counters = $service->countOccurred($snapshotRecord, $source);
        $this->assertEquals(1, $counters['daily']);
        $this->assertEquals(2, $counters['weekly']);
        $this->assertEquals(2, $counters['monthly']);
        $this->assertEquals(2, $counters['yearly']);

        //One of them occurred not in this week
        $incident->time_created = (new \DateTime('now'))->sub(new \DateInterval('P8D'));
        $incident->save();

        $counters = $service->countOccurred($snapshotRecord, $source);
        $this->assertEquals(1, $counters['daily']);
        $this->assertEquals(1, $counters['weekly']);
        $this->assertEquals(2, $counters['monthly']);
        $this->assertEquals(2, $counters['yearly']);

        //One of them occurred not in this month
        $incident->time_created = (new \DateTime('now'))->sub(new \DateInterval('P2M'));
        $incident->save();

        $counters = $service->countOccurred($snapshotRecord, $source);
        $this->assertEquals(1, $counters['daily']);
        $this->assertEquals(1, $counters['weekly']);
        $this->assertEquals(1, $counters['monthly']);
        $this->assertEquals(2, $counters['yearly']);

        //One of them occurred not in this year
        $incident->time_created = (new \DateTime('now'))->sub(new \DateInterval('P2Y'));
        $incident->save();

        $counters = $service->countOccurred($snapshotRecord, $source);
        $this->assertEquals(1, $counters['daily']);
        $this->assertEquals(1, $counters['weekly']);
        $this->assertEquals(1, $counters['monthly']);
        $this->assertEquals(1, $counters['yearly']);
    }

    /**
     * Test delete operation.
     */
    public function testDelete()
    {
        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);
        /** @var SnapshotSource $source */
        $source = $this->container->get(SnapshotSource::class);

        /** @var IncidentSource $incidentSource */
        $incidentSource = $this->container->get(IncidentSource::class);

        $snapshot = $this->makeSnapshot('custom error', 777);
        $snapshotRecord = $this->handleSnapshot($snapshot, true);

        $this->assertCount(1, $source->findWithLast());
        $this->assertNotEmpty($snapshotRecord->getLastIncident());
        $this->assertCount(0, $snapshotRecord->getIncidentsHistory());
        $this->assertCount(1, $incidentSource);
        $this->assertCount(1, $incidentSource->find(['status' => IncidentStatus::LAST]));

        $service->delete($snapshotRecord);

        /** @var SnapshotRecord $snapshotRecord */
        $snapshotRecord = $source->findOne();
        $this->assertNotEmpty($snapshotRecord);

        $this->assertCount(0, $source->findWithLast());
        $this->assertEmpty($snapshotRecord->getLastIncident());
        $this->assertCount(1, $snapshotRecord->getIncidentsHistory());
        $this->assertCount(1, $incidentSource);
        $this->assertCount(1, $incidentSource->find(['status' => IncidentStatus::DELETED]));

        /** @var IncidentRecord $incident */
        $incident = iterator_to_array($snapshotRecord->getIncidentsHistory())[0];

        $this->assertNotEmpty($incident);
        $this->assertEmpty($incident->getExceptionSource());
        $this->assertTrue($incident->status->isDeleted());
    }
}