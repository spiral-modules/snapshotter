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
        $registrator->configure('views', 'namespaces', 'spiral/snapshotter', [
            "'vault' => [",
            "   directory('libraries') . 'spiral/snapshotter/source/views/',",
            "   /*{{namespaces.snapshotter}}*/",
            "]"
        ]);

        $registrator->configure('databases', 'databases', 'spiral/snapshotter', [
            "'vault' => [",
            "   'connection'  => 'vault',",
            "   'tablePrefix' => 'vault_'",
            "   /*{{databases.snapshotter}}*/",
            "]",
        ]);

        $registrator->configure('modules/vault', 'controllers', 'spiral/snapshotter', [
            "'snapshots' => '\\Spiral\\Snapshotter\\Controllers\\SnapshotsController::class'",
        ]);

        $registrator->configure('modules/vault', 'navigation', 'spiral/snapshotter', [
            "'vault'    => [",
            "    'title' => 'Vault',",
            "    'icon'  => 'power_settings_new',",
            "    'items' => [",
            "        /*{{navigation.vault}}*/",
            "    ]",
            "]",
        ]);

        $registrator->configure('modules/vault', 'navigation.vault', 'spiral/snapshotter', [
            "'snapshots' => [",
            "    'title'    => 'Snapshots',",
            "    'requires' => 'keeper.vault.snapshots'",
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