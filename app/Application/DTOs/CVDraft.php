<?php
final class CVDraft
{
  public function __construct(
    public ?string $name = null,
    public ?string $professionalProfile = null,
    public array $workExperience = [],
    public array $studies = [],
    public array $skills = []
  ) {}
}
