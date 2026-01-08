<?php


class CVData
{

  //Hacer Mapper WorkExperience y Studies
  public readonly Name $name;
  public readonly string $professional_profile;
  /** @var WorkExperience[] */
  public $work_experience;
  /** @var Studies[]  */
  public $studies;
  public readonly string $skills;


  public function __construct(Name $name, string $professional_profile, WorkExperience $work_experience, Studies $studies, string $skills)
  {
    $this->name = $name;
    $this->professional_profile = $professional_profile;
    $this->work_experience = $work_experience;
    $this->studies = $studies;
    $this->skills = $skills;
  }
}
