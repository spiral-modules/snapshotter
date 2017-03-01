<?php

namespace Spiral\Snapshotter\Helpers;

use Spiral\Files\FileManager;

class Names
{
    /** @var FileManager */
    private $files;

    /**
     * SnapshotService constructor.
     *
     * @param FileManager $files
     */
    public function __construct(FileManager $files)
    {
        $this->files = $files;
    }

    /**
     * @param string $path
     * @return string
     */
    public function onlyName(string $path): string
    {
        $path = $this->files->normalizePath($path);

        $pos = mb_strripos($path, '/');

        if ($pos !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }
}