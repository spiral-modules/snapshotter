<?php

namespace Spiral\Tests\Snapshotter\AggregationHandler\Sources;

use Spiral\Snapshotter\AggregationHandler\Database\Sources\SnapshotSource;
use Spiral\Snapshotter\AggregationHandler\Services\SnapshotService;
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

        $hash = SnapshotService::makeHash($snapshot);
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

    public function testFindLast()
    {
        /** @var SnapshotSource $source */
        $source = $this->container->get(SnapshotSource::class);

        $this->assertEmpty($source->findLast());

        $snapshot1 = $this->makeSnapshot('custom error1', 777);
        $this->handleSnapshot($snapshot1, true);
        $last1 = $source->findLast();
        $this->assertNotEmpty($last1);

        sleep(1);
        $snapshot2 = $this->makeSnapshot('custom error2', 777);
        $this->handleSnapshot($snapshot2, true);
        $last2 = $source->findLast();
        $this->assertNotEmpty($last2);
        $this->assertNotSame($last1->primaryKey(), $last2->primaryKey());

        sleep(1);
        $snapshot3 = $this->makeSnapshot('custom error2', 777);
        $this->handleSnapshot($snapshot3, true);
        $last3 = $source->findLast();
        $this->assertNotEmpty($last3);
        $this->assertSame($last2->primaryKey(), $last3->primaryKey());
    }

    public function testFindWithLastByPK()
    {
        /** @var SnapshotSource $source */
        $source = $this->container->get(SnapshotSource::class);

        $record = $source->create();
        $record->save();

        $this->assertEmpty($source->findWithLastByPK($record->primaryKey()));

        $snapshot = $this->makeSnapshot('custom error', 777);
        $last = $this->handleSnapshot($snapshot, true);

        $this->assertNotEmpty($last);
        $this->assertNotSame($last->primaryKey(), $record->primaryKey());
        $this->assertNotEmpty($source->findWithLastByPK($last->primaryKey()));
    }
}