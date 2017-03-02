<?php

namespace Spiral\Snapshotter;

use Spiral\Core\Service;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Debug\SnapshotInterface;
use Spiral\Files\FileManager;
use Spiral\Files\FilesInterface;

class FileHandler extends Service implements HandlerInterface
{
    /** @var SnapshotConfig */
    private $config;

    /** @var FileManager */
    private $files;

    /**
     * FileHandler constructor.
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
     * Create snapshot aggregation and aggregated snapshot and tie them together.
     *
     * @param SnapshotInterface $snapshot
     */
    public function registerSnapshot(SnapshotInterface $snapshot)
    {
        if ($this->config->reportingEnabled()) {
            $this->saveSnapshot($snapshot);
        }
    }

    /**
     * Save snapshot information on hard-drive.
     *
     * @param SnapshotInterface $snapshot
     */
    protected function saveSnapshot(SnapshotInterface $snapshot)
    {
        $filename = $this->config->snapshotFilename($snapshot->getException(), time());

        $this->files->write(
            $filename,
            $snapshot->render(),
            FilesInterface::RUNTIME,
            true
        );

        //Rotating files
        $snapshots = $this->files->getFiles($this->config->reportingDirectory());
        if (count($snapshots) > $this->config->maxSnapshots()) {
            $this->performRotation($snapshots);
        }
    }

    /**
     * Clean old snapshots.
     *
     * @param array $snapshots
     */
    protected function performRotation(array $snapshots)
    {
        $oldest = '';
        $oldestTimestamp = PHP_INT_MAX;
        foreach ($snapshots as $snapshot) {
            $snapshotTimestamp = $this->files->time($snapshot);

            if ($snapshotTimestamp < $oldestTimestamp) {
                $oldestTimestamp = $snapshotTimestamp;
                $oldest = $snapshot;
            }
        }

        $this->files->delete($oldest);
    }
}