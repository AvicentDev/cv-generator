<?php

use IteratorAggregate;
use Countable;
use ArrayIterator;

class StudyCollection implements IteratorAggregate, Countable
{
  /** @var Studies[] */
  private array $items = [];

  public function __construct(array $items = [])
  {
    foreach ($items as $item) {
      $this->add($item);
    }
  }

  public function add(Studies $studies): void
  {
    $this->items[] = $studies;
  }

  /**
   * @return Studies[]
   */
  public function all(): array
  {
    return $this->items;
  }

  public function count(): int
  {
    return count($this->items);
  }

  public function getIterator(): ArrayIterator
  {
    return new ArrayIterator($this->items);
  }
}
