<?php
namespace Spiral\Snapshotter\Models;

use Interop\Container\ContainerInterface;
use Spiral\Core\Service;
use Spiral\Snapshotter\Database\Snapshot;
use Spiral\Snapshotter\Database\Sources\SnapshotSource;

/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 13.02.2016
 * Time: 18:00
 */
class SnapshotService extends Service
{
    /**
     * @var SnapshotSource
     */
    private $source = null;

    /**
     * AggregationService constructor.
     *
     * @param SnapshotSource     $source
     * @param ContainerInterface $container
     */
    public function __construct(SnapshotSource $source, ContainerInterface $container)
    {
        $this->source = $source;
        parent::__construct($container);
    }

    /**
     * @param \Exception $exception
     * @param string     $filename
     * @param string     $teaser
     * @param string     $hash
     * @return Snapshot
     */
    public function createFromException(\Exception $exception, $filename, $teaser, $hash)
    {
        $fields = [
            'exception_hash'      => $hash,
            'filename'            => $filename,
            'exception_teaser'    => $teaser,
            'exception_classname' => get_class($exception),
            'exception_message'   => $exception->getMessage(),
            'exception_line'      => $exception->getLine(),
            'exception_file'      => $exception->getFile(),
            'exception_code'      => $exception->getCode(),
        ];

        return $this->source->create($fields);
    }

    /**
     * @return SnapshotSource
     */
    public function getSource()
    {
        return $this->source;
    }
}