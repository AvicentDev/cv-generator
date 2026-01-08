<?php

use App\Domain\Shared\Duration;
use InvalidArgumentException;

class Studies
{
  public readonly string $degree;
  public readonly string $institution;
  public readonly Duration $duration;

  public function __construct(
    string $degree,
    string $institution,
    Duration $duration
  ) {
    $this->ensureDegreeIsValid($degree);
    $this->ensureInstitutionIsValid($institution);

    $this->degree      = $degree;
    $this->institution = $institution;
    $this->duration    = $duration;
  }

  private function ensureDegreeIsValid(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException('Degree cannot be empty.');
    }

    if (strlen($value) > 150) {
      throw new InvalidArgumentException('Degree cannot exceed 150 characters.');
    }
  }

  private function ensureInstitutionIsValid(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException('Institution cannot be empty.');
    }

    if (strlen($value) > 150) {
      throw new InvalidArgumentException('Institution cannot exceed 150 characters.');
    }
  }
}
