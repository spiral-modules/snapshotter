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
        $registrator->configure('databases', 'databases', 'spiral/snapshotter', [
            "'snapshotter' => [",
            "   'connection'  => 'runtime',",
            "   'tablePrefix' => 'snapshots_'",
            "   /*{{databases.snapshotter}}*/",
            "],",
        ]);

        //Register controller in navigation config
        $registrator->configure('modules/vault', 'controllers', 'spiral/snapshotter', [
            "'snapshots' => \\Spiral\\Snapshotter\\AbstractController::class,",
        ]);

        //Register menu item in navigation config
        $registrator->configure('modules/vault', 'navigation.vault', 'spiral/snapshotter', [
            "'snapshots' => [",
            "    'title'    => 'Snapshots',",
            "    'requires' => 'vault.snapshots'",
            "]",
            "/*{{navigation.vault.snapshots}}*/",
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