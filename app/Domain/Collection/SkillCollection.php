<?php

use InvalidArgumentException;
use IteratorAggregate;
use Countable;
use ArrayIterator;

final class SkillCollection implements Countable
{
  /** @var Skill[] */
  private array $items = [];

  public function __construct(array $skills = [])
  {
    foreach ($skills as $skill) {
      $this->add($skill);
    }
  }

  public function value(): array
  {
    return array_map(
      fn(Skill $skill) => $skill->value,
      $this->items
    );
  }


  public function add(Skill $skill): void
  {
    $this->ensureNoDuplicates($skill);

    $this->items[] = $skill;
  }

  /**
   * @return Skill[]
   */
  public function all(): array
  {
    return $this->items;
  }

  public function count(): int
  {
    return count($this->items);
  }
  private function ensureNoDuplicates(Skill $newSkill): void
  {
    foreach ($this->items as $existingSkill) {
      if (strtolower($existingSkill->value) === strtolower($newSkill->value)) {
        throw new InvalidArgumentException('Duplicate skills are not allowed.');
      }
    }
  }

  public static function fromArray(array $items): self
  {
    return new self(
      array_map(
        fn(string $skill) => new Skill($skill),
        $items
      )
    );
  }
}
