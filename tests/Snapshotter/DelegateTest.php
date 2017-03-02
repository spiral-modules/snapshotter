<?php

namespace Spiral\Tests\Snapshotter;

use Mockery\MockInterface;
use Spiral\Debug\LogManager;
use Spiral\Snapshotter\DelegateSnapshot;
use Spiral\Snapshotter\HandlerInterface;
use Spiral\Tests\BaseTest;
use Symfony\Component\Debug\Debug;

class DelegateTest extends BaseTest
{
    /**
     * Test delegation contains snapshot render data.
     */
    public function testSnapshotRenderByPass()
    {
        /** @var HandlerInterface $handler */
        $handler = \Mockery::mock(HandlerInterface::class);

        $delegate = new DelegateSnapshot(
            new \Error('custom error'),
            $this->logs->getLogger(LogManager::DEBUG_CHANNEL),
            $this->container,
            $handler
        );

        $this->assertContains('custom error', $delegate->render());
    }

    /**
     * Test snapshot error was logged.
     */
    public function testSnapshotReport()
    {
        /** @var HandlerInterface|MockInterface $handler */
        $handler = \Mockery::mock(HandlerInterface::class);
        $handler->shouldReceive('registerSnapshot');

        $delegate = new DelegateSnapshot(
            new \Error('custom error'),
            $this->logs->getLogger(LogManager::DEBUG_CHANNEL),
            $this->container,
            $handler
        );

        $this->assertEmpty($this->files->getFiles(directory('runtime') . 'logs/'));
        $delegate->report();
        $this->assertNotEmpty($this->files->getFiles(directory('runtime') . 'logs/'));
    }
}