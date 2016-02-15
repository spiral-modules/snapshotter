<?php
namespace Spiral\Snapshotter\Debug;

use Psr\Log\LoggerInterface;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Debug\Snapshot;
use Spiral\Files\FilesInterface;
use Spiral\Views\ViewsInterface;
use Spiral\Snapshotter\Models\AggregationService;
use Spiral\Snapshotter\Models\SnapshotService;

/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 10.02.2016
 * Time: 20:38
 */
class AggregatedSnapshot extends Snapshot
{
    /**
     * Additional constructor arguments.
     */
    use SaturateTrait;

    /**
     * @var SnapshotConfig
     */
    private $config = null;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * @var SnapshotService
     */
    private $snapshotService;

    /**
     * @var AggregationService
     */
    private $aggregationService;

    /**
     * AggregatedSnapshot constructor.
     *
     * @param \Throwable           $exception
     * @param LoggerInterface|null $logger
     * @param SnapshotConfig|null  $config
     * @param FilesInterface|null  $files
     * @param ViewsInterface|null  $views
     * @param SnapshotService      $snapshotService
     * @param AggregationService   $aggregationService
     */
    public function __construct(
        $exception,
        LoggerInterface $logger = null,
        SnapshotConfig $config = null,
        FilesInterface $files = null,
        ViewsInterface $views = null,
        SnapshotService $snapshotService,
        AggregationService $aggregationService
    ) {
        //todo getConfig(), getLogger()
        $this->config = $this->saturate($config, SnapshotConfig::class);
        $this->logger = $this->saturate($logger, LoggerInterface::class);
        $this->snapshotService = $this->saturate($snapshotService, SnapshotService::class);
        $this->aggregationService = $this->saturate($aggregationService, AggregationService::class);

        parent::__construct($exception, $logger, $config, $files, $views);
    }

    /**
     * @param $string
     * @return string
     */
    private function hash($string)
    {
        return hash('sha256', $string);
    }

    /**
     * {@inheritdoc}
     */
    public function report()
    {
        $this->logger->error($this->getMessage());

        if (!$this->config->reportingEnabled()) {
            //No need to record anything
            return;
        }

        $teaser = $this->getMessage();
        $hash = $this->hash($teaser);
        $exception = $this->getException();
        $filename = $this->config->snapshotFilename($exception, time());

        $snapshot = $this->snapshotService->createFromException(
            $exception,
            $filename,
            $teaser,
            $hash
        );

        $snapshotSource = $this->snapshotService->getSource();
        $snapshotSource->save($snapshot);

        $aggregation = $this->aggregationService->findOrCreateByHash($hash, $teaser);

        $suppress = $snapshotSource->findStored($aggregation)->count()
            ? $aggregation->isSuppressionEnabled()
            : false;

        $this->aggregationService->addSnapshot($aggregation, $snapshot, $suppress);
        $this->aggregationService->getSource()->save($aggregation);

        $snapshotSource->save($snapshot);

        if ($suppress) {
            //No need to create file
            return;
        }

        $this->saveSnapshot();
    }
}