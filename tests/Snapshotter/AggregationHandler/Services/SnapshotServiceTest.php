<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Services;

use Spiral\Models\Accessors\SqlTimestamp;
use Spiral\ORM\Transaction;
use Spiral\ORM\TransactionInterface;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\IncidentSource;
use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
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

        $count = $this->orm->source(SnapshotRecord::class)->find()->count();
        $this->assertEquals(0, $count);

        $hash = 'some hash';
        $snapshot = $service->getByHash($hash);
        $snapshot->save();

        $count = $this->orm->source(SnapshotRecord::class)->find()->count();
        $this->assertEquals(1, $count);

        $hash2 = 'some hash2';
        $snapshot = $service->getByHash($hash2);
        $snapshot->save();

        $count = $this->orm->source(SnapshotRecord::class)->find()->count();
        $this->assertEquals(2, $count);
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
     * Test linking snapshot and aggregation
     */
//    public function testAddSnapshot()
//    {
//        /** @var AggregationService $service */
//        $service = $this->container->get(AggregationService::class);
//
//        /** @var SnapshotRecord $aggregation */
//        $aggregation = new SnapshotRecord();
//        $snapshot = new IncidentRecord();
//
//        $aggregation->save();
//        $snapshot->save();
//
//        $service->addSnapshot($aggregation, $snapshot);
//
//        $aggregation->save();
//        $snapshot->save();
//
//        $this->assertEquals($aggregation->primaryKey(), $snapshot->aggregation_id);
//        $this->assertEquals($snapshot, $aggregation->last_incident);
//    }
}