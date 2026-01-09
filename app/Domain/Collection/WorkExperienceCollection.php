<?php

use App\Domain\Shared\Duration;
use IteratorAggregate;
use Countable;
use ArrayIterator;

class WorkExperienceCollection implements Countable
{
  /** @var WorkExperience[] */
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
      fn(WorkExperience $experience) => $experience->value(),
      $this->items
    );
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

  public static function fromArray(array $items): self
  {
    return new self(
      array_map(
        fn(array $item) => new WorkExperience(
          $item['job_title'],
          $item['company_name'],
          Duration::fromArray($item['duration'])
        ),
        $items
      )
    );
  }
}
