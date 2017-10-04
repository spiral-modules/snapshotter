<?php

namespace Spiral\Tests\Snapshotter;

use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Snapshotter\Bootloaders\FileHandlerBootloader;
use Spiral\Snapshotter\DelegateSnapshot;
use Spiral\Tests\BaseTest;

class FileHandlerTest extends BaseTest
{
    public function testFileRender()
    {
        $this->app->getBootloader()->bootload([FileHandlerBootloader::class]);

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

    public function testRotation()
    {
        /** @var SnapshotConfig $config */
        $config = $this->container->get(SnapshotConfig::class);

        $i = 0;
        $max = $config->maxSnapshots();
        while ($i <= $max + 2) {
            //Create snapshots more than rotation allows
            $snapshot = $this->makeSnapshot('Message i=' . $i, 123);
            $this->handleFileSnapshot($snapshot);

            usleep(500000);
            $i++;
        }

        $this->assertNotEmpty($this->files->getFiles(directory('runtime') . 'snapshots/'));
        $this->assertCount($max, $this->files->getFiles(directory('runtime') . 'snapshots/'));
    }
}