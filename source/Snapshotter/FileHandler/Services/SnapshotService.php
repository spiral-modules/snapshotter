<?php

namespace Spiral\Snapshotter\FileHandler\Services;

use Spiral\Core\Service;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Files\FileManager;
use Spiral\Snapshotter\FileHandler\Entities\Snapshot;

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
     * @return array
     */
    public function getSnapshots()
    {
        $order = [];
        $snapshots = [];

        foreach ($this->files->getFiles($this->config->reportingDirectory(), '*.html') as $file) {
            $snapshots[] = new Snapshot($file, $this->files);
            $order[] = $this->files->time($file);
        }

        array_multisort($order, SORT_DESC, $snapshots);

        return $snapshots;
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
     * @param string $filename
     */
    public function deleteSnapshot(string $filename)
    {
        $this->files->delete($this->config->reportingDirectory() . $filename);
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
     * @param string $filename
     * @return string
     */
    public function read(string $filename): string
    {
        return $this->files->read($this->config->reportingDirectory() . $filename);
    }
}