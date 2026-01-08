<?php

use InvalidArgumentException;

class ProfessionalProfile
{
  public readonly string $value;

  public function __construct(string $value)
  {
    $this->ensureProfileIsValid($value);

    $this->value = $value;
  }

  private function ensureProfileIsValid(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException('Professional profile cannot be empty.');
    }

    if (strlen($value) < 30) {
      throw new InvalidArgumentException('Professional profile must be at least 30 characters long.');
    }

    if (strlen($value) > 500) {
      throw new InvalidArgumentException('Professional profile cannot exceed 500 characters.');
    }
  }
}
