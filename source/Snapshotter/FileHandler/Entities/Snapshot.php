<?php

namespace Spiral\Snapshotter\FileHandler\Entities;

use Spiral\Files\FileManager;

class Snapshot
{
    /** @var string */
    private $filename;

    /** @var FileManager */
    private $files;

    public function __construct(string $filename, FileManager $files)
    {
        $this->filename = $filename;
        $this->files = $files;
    }

    public function path()
    {
        return $this->filename;
    }

    public function id()
    {
        return basename($this->filename);
    }

    public function timestamp()
    {
        return $this->files->time($this->filename);
    }
}