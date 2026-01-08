<?php

namespace App\Domain\Shared;

use DateInterval;
use InvalidArgumentException;

final class Duration
{
  private DateInterval $interval;

  public function __construct(DateInterval $interval)
  {
    $this->ensureIsValid($interval);
    $this->interval = $interval;
  }

  public static function fromString(string $value): self
  {
    return new self(new DateInterval($value));
  }

  private function ensureIsValid(DateInterval $interval): void
  {
    if (
      $interval->invert === 1 ||
      ($interval->y === 0 && $interval->m === 0 && $interval->d === 0)
    ) {
      throw new InvalidArgumentException('Duration must be positive.');
    }
  }

  public function value(): DateInterval
  {
    return $this->interval;
  }
}
