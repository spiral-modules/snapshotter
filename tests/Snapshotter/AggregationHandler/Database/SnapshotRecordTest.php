<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Database;

use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Tests\BaseTest;

class SnapshotRecordTest extends BaseTest
{
    public function testSuppressionState()
    {
        $snapshot = $this->makeSnapshot('message', 123);
        $record = $this->handleSnapshot($snapshot, true);

        $this->assertFalse($record->isSuppressionEnabled());

        $record->setSuppression(true);
        $this->assertTrue($record->isSuppressionEnabled());

        $record->setSuppression(false);
        $this->assertFalse($record->isSuppressionEnabled());
    }

    public function testSuppress()
    {
        $snapshot = $this->makeSnapshot('message', 123);

        $this->handleSnapshot($snapshot, true);
        $record = $this->handleSnapshot($snapshot, true);

        $this->assertEquals(2, $record->count_occurred);
        $this->assertCount(1, $record->getIncidentsHistory());

        /** @var IncidentRecord $incident */
        $incident = iterator_to_array($record->getIncidentsHistory())[0];

        $this->assertNotEmpty($incident);
        $this->assertNotEmpty($incident->getExceptionSource());
        $this->assertTrue($incident->status->isStored());

        //Suppress new history record
        $record->setSuppression(true);
        $record->save();

        $record = $this->handleSnapshot($snapshot, true);

        $this->assertEquals(3, $record->count_occurred);
        $this->assertCount(2, $record->getIncidentsHistory());

        $incident = iterator_to_array($record->getIncidentsHistory())[0];

        $this->assertNotEmpty($incident);
        $this->assertNotEmpty($incident->getExceptionSource());
        $this->assertTrue($incident->status->isStored());

        $incident = iterator_to_array($record->getIncidentsHistory())[1];

        $this->assertNotEmpty($incident);
        $this->assertEmpty($incident->getExceptionSource());
        $this->assertFalse($incident->status->isStored());
        $this->assertTrue($incident->status->isSuppressed());

        //Don't suppress new history record
        $record->setSuppression(false);
        $record->save();

        $record = $this->handleSnapshot($snapshot, true);

        $this->assertEquals(4, $record->count_occurred);
        $this->assertCount(3, $record->getIncidentsHistory());

        $incident = iterator_to_array($record->getIncidentsHistory())[0];

        $this->assertNotEmpty($incident);
        $this->assertNotEmpty($incident->getExceptionSource());
        $this->assertTrue($incident->status->isStored());

        $incident = iterator_to_array($record->getIncidentsHistory())[1];

        $this->assertNotEmpty($incident);
        $this->assertEmpty($incident->getExceptionSource());
        $this->assertFalse($incident->status->isStored());
        $this->assertTrue($incident->status->isSuppressed());

        $incident = iterator_to_array($record->getIncidentsHistory())[2];

        $this->assertNotEmpty($incident);
        $this->assertNotEmpty($incident->getExceptionSource());
        $this->assertTrue($incident->status->isStored());
    }
}