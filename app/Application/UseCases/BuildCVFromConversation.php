<?php
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
