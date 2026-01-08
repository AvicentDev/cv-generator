<?php
class CVData
{

  public readonly Name $name;
  public readonly ProfessionalProfile $professional_profile;
  public readonly WorkExperienceCollection $work_experience;
  public readonly StudyCollection $studies;
  public readonly SkillCollection $skills;


  public function __construct(Name $name, ProfessionalProfile $professional_profile, WorkExperienceCollection $work_experience, StudyCollection $studies, SkillCollection $skills)
  {
    $this->name = $name;
    $this->professional_profile = $professional_profile;
    $this->work_experience = $work_experience;
    $this->studies = $studies;
    $this->skills = $skills;
  }


  public function addWorkExperience(WorkExperience $experience): void
  {
    if ($this->work_experience->count() >= 10) {
      throw new DomainException('Maximum 10 work experiences allowed');
    }

    $this->work_experience->add($experience);
  }

  public function addStudy(Studies $study)
  {
    if ($this->studies->count() >= 4) {
      throw new DomainException('Maximum 4 studies experiences allowed');
    }

    $this->studies->add($study);
  }

  public function addSkill(Skill $skill)
  {
    if ($this->skills->count() >= 8) {
      throw new DomainException('Maximum 8 skills allowed');
    }

    $this->skills->add($skill);
  }
}
