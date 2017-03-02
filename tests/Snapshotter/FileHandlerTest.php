<?php

namespace Spiral\Tests\Snapshotter;

use Spiral\Snapshotter\AggregationHandler;
use Spiral\Snapshotter\AggregationHandler\Database\IncidentRecord;
use Spiral\Snapshotter\AggregationHandler\Database\SnapshotRecord;
use Spiral\Snapshotter\Bootloaders\FileSnapshotterBootloader;
use Spiral\Snapshotter\DelegateSnapshot;
use Spiral\Tests\BaseTest;

class FileHandlerTest extends BaseTest
{
    public function testFileRender()
    {
        $this->app->getBootloader()->bootload([FileSnapshotterBootloader::class]);

        $snapshot = $this->makeSnapshot('File error', 123);
        /** @var DelegateSnapshot $delegate */
        $delegate = $this->factory->make(DelegateSnapshot::class, [
            'exception' => $snapshot->getException()
        ]);

        $this->assertEmpty($this->files->getFiles(directory('runtime') . 'logs/'));
        $this->assertEmpty($this->files->getFiles(directory('runtime') . 'snapshots/'));

        $delegate->report();

        $this->assertNotEmpty($this->files->getFiles(directory('runtime') . 'logs/'));
        $this->assertNotEmpty($this->files->getFiles(directory('runtime') . 'snapshots/'));
    }
}