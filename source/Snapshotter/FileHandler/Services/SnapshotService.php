<?php

namespace Spiral\Snapshotter\FileHandler\Services;

use Spiral\Core\Service;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Files\FileManager;
use Spiral\Snapshotter\FileHandler\Entities\FileSnapshot;
use Spiral\Snapshotter\FileHandler\Sources\ArraySource;

class SnapshotService extends Service
{
    /** @var SnapshotConfig */
    private $config;

    /** @var FileManager */
    private $files;

    /**
     * SnapshotService constructor.
     *
     * @param SnapshotConfig $config
     * @param FileManager    $files
     */
    public function __construct(SnapshotConfig $config, FileManager $files)
    {
        $this->config = $config;
        $this->files = $files;
    }

    /**
     * Get snapshots.
     *
     * @return ArraySource
     */
    public function getSnapshots(): ArraySource
    {
        $order = [];
        $snapshots = [];

        if (!$this->files->exists($this->config->reportingDirectory())) {
            return new ArraySource();
        }

        foreach ($this->files->getFiles($this->config->reportingDirectory(), '*.html') as $file) {
            $snapshots[] = new FileSnapshot($file);
            $order[] = $this->files->time($file);
        }

        array_multisort($order, SORT_DESC, $snapshots);

        return new ArraySource($snapshots);
    }

    /**
     * @param string $filename
     * @return null|FileSnapshot
     */
    public function getSnapshot(string $filename)
    {
        if (!$this->exists($filename)) {
            return null;
        }

        return new FileSnapshot($this->config->reportingDirectory() . $filename);
    }

    /**
     *
     */
    public function deleteSnapshots()
    {
        foreach ($this->files->getFiles($this->config->reportingDirectory(), '*.html') as $file) {
            $this->files->delete($file);
        }
    }

    /**
     * @param FileSnapshot $filename
     */
    public function deleteSnapshot(FileSnapshot $filename)
    {
        $this->files->delete($filename->path());
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function exists(string $filename): bool
    {
        return $this->files->exists($this->config->reportingDirectory() . $filename);
    }

    /**
     * @param FileSnapshot $snapshot
     * @return string
     */
    public function read(FileSnapshot $snapshot): string
    {
        return $this->files->read($snapshot->path());
    }
}