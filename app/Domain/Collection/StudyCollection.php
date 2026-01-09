<?php

use App\Domain\Shared\Duration;
use IteratorAggregate;
use Countable;
use ArrayIterator;

class StudyCollection implements Countable
{
  /** @var Studies[] */
  private array $items = [];

  public function __construct(array $items = [])
  {
    foreach ($items as $item) {
      $this->add($item);
    }
  }

  public function value(): array
  {
    return array_map(
      fn(Studies $study) => $study->value(),
      $this->items
    );
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

  public static function fromArray(array $items): self
  {
    return new self(
      array_map(
        fn(array $item) => new Studies(
          $item['degree'],
          $item['institution'],
          Duration::fromArray($item['duration'])
        ),
        $items
      )
    );
  }
}
