<?php

namespace Spiral\Snapshotter\FileHandler\Entities;

class FileSnapshot
{
    /** @var string */
    private $filename;

    /**
     * FileSnapshot constructor.
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return basename($this->filename);
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return filesize($this->filename);
    }

    /**
     * @return FileTimestamp
     */
    public function timestamp(): FileTimestamp
    {
        return new FileTimestamp(filemtime($this->filename), []);
    }
}