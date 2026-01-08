<?php

use App\Domain\Shared\Duration;
use InvalidArgumentException;;

class WorkExperience
{
  public readonly string $job_title;
  public readonly string $company_name;
  public readonly Duration $duration;


  public function __construct(
    string $job_title,
    string $company_name,
    Duration $duration
  ) {
    $this->ensureJobTitleIsValid($job_title);
    $this->ensureCompanyNameIsValid($company_name);

    $this->job_title   = $job_title;
    $this->company_name = $company_name;
    $this->duration = $duration;
  }

  private function ensureJobTitleIsValid(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException('Job title cannot be empty.');
    }
    if (strlen($value) > 100) {
      throw new InvalidArgumentException('Job title cannot exceed 100 characters.');
    }
  }

  private function ensureCompanyNameIsValid(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException('Company name cannot be empty.');
    }
    if (strlen($value) > 100) {
      throw new InvalidArgumentException('Company name cannot exceed 100 characters.');
    }
  }
}
