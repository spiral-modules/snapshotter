<?php

namespace Spiral\Tests\Snapshotter\FileHandler\Entities;

use Spiral\Snapshotter\FileHandler\Entities\FileSnapshot;
use Spiral\Snapshotter\FileHandler\Entities\FileTimestamp;
use Spiral\Tests\BaseTest;

class FileSnapshotTest extends BaseTest
{
    public function testEntity()
    {
        $filename = __FILE__;
        $entity = new FileSnapshot($filename);

        $this->assertEquals(basename($filename), $entity->id());
        $this->assertEquals($filename, $entity->path());
        $this->assertInstanceOf(FileTimestamp::class, $entity->timestamp());
    }
}