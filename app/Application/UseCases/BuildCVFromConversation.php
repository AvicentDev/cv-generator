<?php

namespace App\Application\UseCases;

use App\Domain\Collection\SkillCollection;
use App\Domain\Collection\StudyCollection;
use App\Domain\Collection\WorkExperienceCollection;
use App\Domain\CV\CVData;
use App\Domain\VO\Name;
use App\Domain\VO\ProfessionalProfile;

class BuildCVFromConversation
{
  public function build(object $draft): CVData
  {
    return new CVData(
      new Name($draft->name),
      new ProfessionalProfile($draft->professionalProfile),
      WorkExperienceCollection::fromArray($draft->workExperience),
      StudyCollection::fromArray($draft->studies),
      SkillCollection::fromArray($draft->skills),
    );
  }
}
