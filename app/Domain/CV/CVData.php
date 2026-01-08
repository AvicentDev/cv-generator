<?php
class CVData
{

  //Hacer Mapper WorkExperience y Studies
  public readonly Name $name;
  public readonly ProfessionalProfile $professional_profile;
  public WorkExperienceCollection $work_experience;
  public StudiyCollection $studies;
  public readonly SkillCollection $skills;


  public function __construct(Name $name, ProfessionalProfile $professional_profile, WorkExperienceCollection $work_experience, StudiyCollection $studies, SkillCollection $skills)
  {
    $this->name = $name;
    $this->professional_profile = $professional_profile;
    $this->work_experience = $work_experience;
    $this->studies = $studies;
    $this->skills = $skills;
  }
}
