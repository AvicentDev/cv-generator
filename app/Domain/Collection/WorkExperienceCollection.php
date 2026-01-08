<?php

use IteratorAggregate;
use Countable;
use ArrayIterator;

class WorkExperienceCollection implements IteratorAggregate, Countable
{
  /** @var WorkExperience[] */
  private array $items = [];

  public function __construct(array $items = [])
  {
    foreach ($items as $item) {
      $this->add($item);
    }
  }

  public function add(WorkExperience $experience): void
  {
    $this->items[] = $experience;
  }

  /**
   * @return WorkExperience[]
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
