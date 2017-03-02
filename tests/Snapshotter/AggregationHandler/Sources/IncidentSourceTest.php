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

        $incident->delete();

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
        $this->assertCount(1, $source->findBySnapshotWithSource());
        $this->assertCount(1, $source->findBySnapshotWithSource($snapshotRecord));

        $snapshotRecord = $this->handleSnapshot($snapshot, true);

        $this->assertCount(2, $source->find());
        $this->assertCount(2, $source->findBySnapshotWithSource());
        $this->assertCount(2, $source->findBySnapshotWithSource($snapshotRecord));
    }

    public function testFindSnapshotHistory()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);

        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);

        $snapshotRecord = $this->handleSnapshot($snapshot, true);
        $this->assertCount(0, $source->findSnapshotHistory($snapshotRecord));

        $snapshotRecord = $this->handleSnapshot($snapshot, true);
        $this->assertCount(1, $source->findSnapshotHistory($snapshotRecord));

        /** @var IncidentRecord $incident */
        $incident = $source->find()->orderBy('time_created', 'ASC')->findOne();
        $this->assertNotEmpty($incident);

        $incident->status->setDeleted();
        $incident->save();

        $this->assertCount(0, $source->findSnapshotHistory($snapshotRecord));

        $incident->status->setSuppressed();
        $incident->save();

        $this->assertCount(1, $source->findSnapshotHistory($snapshotRecord));
    }

    public function testFinsStoredBySnapshotByPK()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);

        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);

        $snapshotRecord = $this->handleSnapshot($snapshot, true);
        $snapshotRecord = $this->handleSnapshot($snapshot, true);

        /** @var IncidentRecord $incident */
        $incident = $source->find()->orderBy('time_created', 'ASC')->findOne();
        $this->assertNotEmpty($incident);

        $this->assertNotEmpty(
            $source->findStoredBySnapshotByPK($snapshotRecord, $incident->primaryKey())
        );

        //Change status
        $incident->status->setSuppressed();
        $incident->save();

        $this->assertEmpty(
            $source->findStoredBySnapshotByPK($snapshotRecord, $incident->primaryKey())
        );

        //Correct id
        $this->assertEmpty(
            $source->findStoredBySnapshotByPK($snapshotRecord, $incident->primaryKey() + 20)
        );

        //Change hash and change status back
        $snapshotRecord->exception_hash = 'some hash';
        $snapshotRecord->save();

        $incident->status->setStored();
        $incident->save();

        $this->assertEmpty(
            $source->findStoredBySnapshotByPK($snapshotRecord, $incident->primaryKey())
        );
    }

    public function testFindBySnapshotByPK()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);

        /** @var IncidentSource $source */
        $source = $this->container->get(IncidentSource::class);

        $snapshotRecord = $this->handleSnapshot($snapshot, true);
        $snapshotRecord = $this->handleSnapshot($snapshot, true);

        /** @var IncidentRecord $incident */
        $incident = $source->find()->orderBy('time_created', 'ASC')->findOne();
        $this->assertNotEmpty($incident);

        $this->assertNotEmpty(
            $source->findBySnapshotByPK($snapshotRecord, $incident->primaryKey())
        );

        //Correct id
        $this->assertEmpty(
            $source->findBySnapshotByPK($snapshotRecord, $incident->primaryKey() + 20)
        );

        //Change hash
        $snapshotRecord->exception_hash = 'some hash';
        $snapshotRecord->save();

        $this->assertEmpty(
            $source->findBySnapshotByPK($snapshotRecord, $incident->primaryKey())
        );
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