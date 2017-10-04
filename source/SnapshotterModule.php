<?php

namespace Spiral;

use Spiral\Core\DirectoriesInterface;
use Spiral\Modules\ModuleInterface;
use Spiral\Modules\PublisherInterface;
use Spiral\Modules\RegistratorInterface;

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Valentin V (vvval)
 */
class SnapshotterModule implements ModuleInterface
{
    /**
     * @param RegistratorInterface $registrator
     */
    public function register(RegistratorInterface $registrator)
    {
        //Register tokenizer directory
        $registrator->configure('tokenizer', 'directories', 'spiral/snapshotter', [
            "directory('libraries') . 'spiral/snapshotter/source/Snapshotter/',",
        ]);

        //Register view namespace
        $registrator->configure('views', 'namespaces', 'spiral/snapshotter', [
            "'snapshotter' => [",
            "   directory('libraries') . 'spiral/snapshotter/source/views/',",
            "   /*{{namespaces.snapshotter}}*/",
            "],"
        ]);

        //Register database settings
        $registrator->configure('databases', 'aliases', 'spiral/snapshotter', [
            "'snapshots' => 'default',"
        ]);

        //Register controller in navigation config
        $registrator->configure('modules/vault', 'controllers', 'spiral/snapshotter', [
            "'snapshots' => \\Spiral\\Snapshotter\\AbstractController::class,",
        ]);
    }

    /**
     * @param PublisherInterface   $publisher
     * @param DirectoriesInterface $directories
     */
    public function publish(PublisherInterface $publisher, DirectoriesInterface $directories)
    {
    }
}