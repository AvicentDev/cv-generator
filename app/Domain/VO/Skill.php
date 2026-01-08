<?php

use InvalidArgumentException;

class Skill
{
  public readonly string $value;

  public function __construct(string $value)
  {
    $this->ensureSkillIsValid($value);

    $this->value = $value;
  }

  private function ensureSkillIsValid(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException('Skill cannot be empty.');
    }

    if (strlen($value) > 50) {
      throw new InvalidArgumentException('Skill cannot exceed 50 characters.');
    }
  }
}
