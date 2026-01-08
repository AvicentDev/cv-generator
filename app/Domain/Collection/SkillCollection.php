<?php

use InvalidArgumentException;
use IteratorAggregate;
use Countable;
use ArrayIterator;

final class SkillCollection implements IteratorAggregate, Countable
{
  /** @var Skill[] */
  private array $items = [];

  public function __construct(array $skills = [])
  {
    foreach ($skills as $skill) {
      $this->add($skill);
    }
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

  public function getIterator(): ArrayIterator
  {
    return new ArrayIterator($this->items);
  }

  private function ensureNoDuplicates(Skill $newSkill): void
  {
    foreach ($this->items as $existingSkill) {
      if (strtolower($existingSkill->value) === strtolower($newSkill->value)) {
        throw new InvalidArgumentException('Duplicate skills are not allowed.');
      }
    }
  }
}
