<?php

class BuildCVFromConversation
{
  public function build(array $cv_data): CVData
  {
    return new CVData(
      new Name($cv_data['data']['name']),
      new ProfessionalProfile($cv_data['data']['professional_profile']),
      WorkExperienceCollection::fromArray($cv_data['data']['work_experience']),
      StudyCollection::fromArray($cv_data['data']['studies']),
      SkillCollection::fromArray($cv_data['data']['skills'])
    );
  }
}
