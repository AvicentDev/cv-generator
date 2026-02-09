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

    $years = (int) $data['years'];
    $months = (int) $data['months'];
    $days = (int) $data['days'];

    // Construir el string ISO 8601 correctamente
    $durationString = 'P';
    if ($years > 0) {
      $durationString .= $years . 'Y';
    }
    if ($months > 0) {
      $durationString .= $months . 'M';
    }
    if ($days > 0) {
      $durationString .= $days . 'D';
    }

    // Si no hay ningún valor, usar P0D
    if ($durationString === 'P') {
      $durationString = 'P0D';
    }

    $interval = new DateInterval($durationString);

    return new self($interval);
  }

  public function toString(): string
  {
    $parts = [];

    if ($this->interval->y > 0) {
      $parts[] = $this->interval->y . ' año' . ($this->interval->y > 1 ? 's' : '');
    }

    if ($this->interval->m > 0) {
      $parts[] = $this->interval->m . ' mes' . ($this->interval->m > 1 ? 'es' : '');
    }

    if ($this->interval->d > 0) {
      $parts[] = $this->interval->d . ' día' . ($this->interval->d > 1 ? 's' : '');
    }

    return implode(' ', $parts);
  }



  private function ensureIsValid(DateInterval $interval): void
  {
    if ($interval->invert === 1) {
      throw new InvalidArgumentException('Duration must be positive.');
    }
  }

  public function value(): DateInterval
  {
    return $this->interval;
  }
}
