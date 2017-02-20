<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Sources;

use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\AggregationHandler;
use Spiral\Tests\BaseTest;

class SnapshotSourceTest extends BaseTest
{
    /**
     * Find snapshot record by hash.
     */
    public function testFindByHash()
    {
        $snapshot = $this->makeSnapshot('custom error', 777);
        $this->handleSnapshot($snapshot, true);

        $hash = AggregationHandler\Services\SnapshotService::makeHash($snapshot);
        $hash2 = 'some second random hash';

        /** @var SnapshotSource $source */
        $source = $this->container->get(SnapshotSource::class);
        $this->assertNotEmpty($source->findByHash($hash));
        $this->assertEmpty($source->findByHash($hash2));

        $record = $source->findByHash($hash);
        $record->exception_hash = $hash2;
        $record->save();

        $this->assertEmpty($source->findByHash($hash));
        $this->assertNotEmpty($source->findByHash($hash2));
    }
}