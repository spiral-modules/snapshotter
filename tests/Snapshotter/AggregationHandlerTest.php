<?php

namespace Spiral\Tests\Snapshotter;

use Spiral\Snapshotter\AggregationHandler;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Tests\BaseTest;

class AggregationHandlerTest extends BaseTest
{
    /**
     * Snapshot and incident record are successfully created
     */
    public function testFirstOccurrence()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);
        $this->assertSame(0, $this->orm->source(SnapshotRecord::class)->count());

        $this->handleSnapshot($snapshot, true);
        $this->assertSame(1, $this->orm->source(SnapshotRecord::class)->count());
        $this->assertSame(1, $this->orm->source(IncidentRecord::class)->count());
    }

    /**
     * Snapshot has several incidents
     */
    public function testSecondOccurrence()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);
        $this->assertSame(0, $this->orm->source(SnapshotRecord::class)->count());

        $snapshotRecord = $this->handleSnapshot($snapshot, true);
        $this->assertSame(1, $this->orm->source(SnapshotRecord::class)->count());
        $this->assertSame(1, $this->orm->source(IncidentRecord::class)->count());

        //After first incident history is empty
        $this->assertCount(0, $snapshotRecord->getIncidentsHistory());

        $snapshotRecord = $this->handleSnapshot($snapshot, true);
        $this->assertSame(1, $this->orm->source(SnapshotRecord::class)->count());
        $this->assertSame(2, $this->orm->source(IncidentRecord::class)->count());
        $this->assertCount(1, $snapshotRecord->getIncidentsHistory());
    }

    /**
     * Several snapshots occurred
     * Snapshots successfully aggregated by snapshot teaser.
     * Format: "{exception class}: {message} in {file} at line {line}"
     */
    public function testAnotherOccurrence()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);
        $this->assertSame(0, $this->orm->source(SnapshotRecord::class)->count());

        $this->handleSnapshot($snapshot, true);
        $this->assertSame(1, $this->orm->source(SnapshotRecord::class)->count());
        $this->assertSame(1, $this->orm->source(IncidentRecord::class)->count());

        $snapshot2 = $this->makeSnapshot('another custom error', 777);

        $this->handleSnapshot($snapshot2, true);
        $this->assertSame(2, $this->orm->source(SnapshotRecord::class)->count());
        $this->assertSame(2, $this->orm->source(IncidentRecord::class)->count());
    }

    /**
     * Incident data is correct
     */
    public function testIncidentIntegrity()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);
        $this->assertSame(0, $this->orm->source(SnapshotRecord::class)->count());

        $snapshotRecord = $this->handleSnapshot($snapshot, true);

        /*
         * Can't test $snapshot->render() because it renders on demand so content is not equal by nature
         */
        $lastIncident = $snapshotRecord->getLastIncident();
        $this->assertNotEmpty($lastIncident);

        $this->assertEquals(true, $lastIncident->status->isLast());

        $this->assertEquals(
            get_class($snapshot->getException()),
            $lastIncident->getExceptionClass()
        );

        $this->assertEquals(
            $snapshot->getException()->getMessage(),
            $lastIncident->getExceptionMessage()
        );

        $this->assertEquals(
            $snapshot->getMessage(),
            $lastIncident->getExceptionTeaser()
        );

        $this->assertEquals(
            AggregationHandler\Services\SnapshotService::makeHash($snapshot),
            $lastIncident->getExceptionHash()
        );

        $this->assertEquals(
            $snapshot->getException()->getCode(),
            $lastIncident->getExceptionCode()
        );

        $this->assertEquals(
            $snapshot->getException()->getFile(),
            $lastIncident->getExceptionFile()
        );

        $this->assertEquals(
            $snapshot->getException()->getLine(),
            $lastIncident->getExceptionLine()
        );
    }
}