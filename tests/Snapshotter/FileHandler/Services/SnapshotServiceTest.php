<?php

namespace Spiral\Tests\Snapshotter\FileHandler\Services;

use Spiral\Snapshotter\FileHandler\Entities\FileSnapshot;
use Spiral\Snapshotter\FileHandler\Services\SnapshotService;
use Spiral\Tests\BaseTest;

class SnapshotServiceTest extends BaseTest
{
    public function testGetSnapshots()
    {
        $snapshot = $this->makeSnapshot('Message', 123);
        $this->handleFileSnapshot($snapshot);

        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);

        $this->assertCount(1, $service->getSnapshots());

        sleep(1);
        $snapshot = $this->makeSnapshot('Message2', 456);
        $this->handleFileSnapshot($snapshot);

        $this->assertCount(2, $service->getSnapshots());
    }

    public function testGetSnapshot()
    {
        $snapshot = $this->makeSnapshot('Message', 123);
        $this->handleFileSnapshot($snapshot);

        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);

        /** @var FileSnapshot $file */
        $file = current($service->getSnapshots()->iterate());
        $this->assertInstanceOf(FileSnapshot::class, $file);
        $filename = $file->id();

        $this->assertNotEmpty($service->getSnapshot($filename));
        $this->assertInstanceOf(FileSnapshot::class, $service->getSnapshot($filename));
    }

    public function testRead()
    {
        $snapshot = $this->makeSnapshot('Message', 123);
        $this->handleFileSnapshot($snapshot);

        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);

        /** @var FileSnapshot $file */
        $file = current($service->getSnapshots()->iterate());

        $this->assertNotEmpty($service->read($file));
    }

    public function testExists()
    {
        $snapshot = $this->makeSnapshot('Message', 123);
        $this->handleFileSnapshot($snapshot);

        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);

        /** @var FileSnapshot $file */
        $file = current($service->getSnapshots()->iterate());

        $this->assertTrue($service->exists($file->id()));
        $this->assertFalse($service->exists('some name'));
    }

    public function testDelete()
    {
        $snapshot = $this->makeSnapshot('Message', 123);
        $this->handleFileSnapshot($snapshot);

        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);

        /** @var FileSnapshot $file */
        $file = current($service->getSnapshots()->iterate());

        $this->assertTrue($service->exists($file->id()));

        $service->deleteSnapshot($file);
        $this->assertFalse($service->exists($file->id()));
    }

    public function testDeleteSnapshots()
    {
        $snapshot = $this->makeSnapshot('Message', 123);
        $this->handleFileSnapshot($snapshot);

        sleep(1);
        $snapshot = $this->makeSnapshot('Message2', 456);
        $this->handleFileSnapshot($snapshot);

        /** @var SnapshotService $service */
        $service = $this->container->get(SnapshotService::class);

        $this->assertCount(2, $service->getSnapshots());

        $service->deleteSnapshots();

        $this->assertCount(0, $service->getSnapshots());
    }
}