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

    /**
     * ArraySource constructor.
     *
     * @param array $source
     */
    public function __construct(array $source = [])
    {
        $this->source = $source;
    }

    /**
     * @param bool $paginate
     * @return array
     */
    public function run(bool $paginate = true): array
    {
        $clone = clone $this;

        if ($paginate && $clone->hasPaginator()) {
            return array_slice(
                $clone->source,
                $clone->getPaginator()->getOffset(),
                $clone->getPaginator()->getLimit()
            );
        }

        return $clone->source;
    }

    /**
     * @return array
     */
    public function getIterator(): array
    {
        return $this->run();
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->run(false));
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function current()
    {
        return $this->source[$this->cursor];
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
        return $this->cursor < count($this->run());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->cursor = 0;
    }
}