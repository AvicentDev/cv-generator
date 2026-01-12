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

  public static function fromArray(array $data): self
  {
    if (
      !isset($data['years'], $data['months'], $data['days'])
    ) {
      throw new InvalidArgumentException('Invalid duration data.');
    }

    $interval = new DateInterval('P0D');
    $interval->y = (int) $data['years'];
    $interval->m = (int) $data['months'];
    $interval->d = (int) $data['days'];

    return new self($interval);
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
