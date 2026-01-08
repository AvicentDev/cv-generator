<?php

use InvalidArgumentException;

final class SkillCollection implements IteratorAggregate
{
  /** @var Skill[] */
  private array $skills;

  public function __construct(array $skills)
  {
    $this->ensureAllAreSkills($skills);
    $this->ensureNotEmpty($skills);
    $this->ensureNoDuplicates($skills);

    $this->skills = $skills;
  }

  public function getIterator(): Traversable
  {
    return new ArrayIterator($this->skills);
  }

  /** @return Skill[] */
  public function all(): array
  {
    return $this->skills;
  }

  private function ensureAllAreSkills(array $skills): void
  {
    foreach ($skills as $skill) {
      if (!$skill instanceof Skill) {
        throw new InvalidArgumentException('All elements must be Skill.');
      }
    }
  }

  private function ensureNotEmpty(array $skills): void
  {
    if (count($skills) === 0) {
      throw new InvalidArgumentException('At least one skill is required.');
    }
  }

  private function ensureNoDuplicates(array $skills): void
  {
    $values = array_map(
      fn(Skill $skill) => strtolower($skill->value),
      $skills
    );

    if (count($values) !== count(array_unique($values))) {
      throw new InvalidArgumentException('Duplicate skills are not allowed.');
    }
  }
}
