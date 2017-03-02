<?php

namespace Spiral\Tests\Snapshotter;

use Spiral\Snapshotter\FileHandler\Entities\FileTimestamp;
use Spiral\Snapshotter\Helpers\Names;
use Spiral\Snapshotter\Helpers\Timestamps;
use Spiral\Tests\BaseTest;

class HelpersTest extends BaseTest
{
    public function testName()
    {
        /** @var Names $names */
        $names = $this->container->get(Names::class);

        $this->assertEquals('filename', $names->onlyName('filename'));
        $this->assertEquals('filename', $names->onlyName('filename/'));
        $this->assertEquals('filename', $names->onlyName('some/path/to/filename'));
        $this->assertEquals('filename', $names->onlyName('/some/path/to/filename/'));
        $this->assertEquals('filename', $names->onlyName('C:\some/path/to/filename'));
        $this->assertEquals('filename', $names->onlyName('some\path/to\filename'));
    }

    public function testTimestamp()
    {
        /** @var Timestamps $names */
        $names = $this->container->get(Timestamps::class);

        $timestamp = new FileTimestamp(new \DateTime(), []);

        $this->assertEquals($timestamp, $names->getTime($timestamp));
        $this->assertNotEquals('filename', $names->getTime($timestamp, true));
    }
}