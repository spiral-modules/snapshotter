<?php

namespace Spiral\Snapshotter\FileHandler\Sources;

use Spiral\Core\Component;
use Spiral\Pagination\PaginatorAwareInterface;
use Spiral\Pagination\Traits\PaginatorTrait;

class ArraySource extends Component implements \Countable, PaginatorAwareInterface, \Iterator
{
    use PaginatorTrait;

    /** @var array */
    protected $source = [];

    /** @var int */
    private $cursor;

    /** @var array */
    private $keys = [];

    /**
     * ArraySource constructor.
     *
     * @param array $source
     */
    public function __construct(array $source = [])
    {
        $this->source = $source;
        $this->keys = array_keys($this->source);
    }

    /**
     * @param bool $paginate
     * @return array
     */
    public function iterate(bool $paginate = true): array
    {
        if ($paginate && $this->hasPaginator()) {
            return array_slice(
                $this->source,
                $this->getPaginator()->getOffset(),
                $this->getPaginator()->getLimit()
            );
        }

        return $this->source;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->iterate(false));
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function current()
    {
        $offset = $this->hasPaginator() ? $this->getPaginator()->getOffset() : 0;
        $key = $this->keys[$this->key() + $offset];

        return $this->source[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->cursor++;
    }

    /**
     * {@inheritdoc}
     *
     * @return
     */
    public function key(): int
    {
        return $this->cursor;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->key() < count($this->iterate());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->cursor = 0;
    }
}