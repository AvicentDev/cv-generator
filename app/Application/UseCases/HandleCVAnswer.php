<?php

class HandleCVAnswer implements HandleCVAnswerInterface
{
  public function handle(array $cv_data, string $answer): array
  {
    $step = $cv_data['step'] ?? 'name';

    match ($step) {
      'name' => $this->handleName($cv_data, $answer),
      'professional_profile' => $this->handleProfessionalProfile($cv_data, $answer),
      'work_experience' => $this->handleWorkExperience($cv_data, $answer),
      'studies' => $this->handleStudies($cv_data, $answer),
      'skills' => $this->handleSkills($cv_data, $answer),
      default => $cv_data,
    };

    return $cv_data;
  }

  private function handleName(array &$cv_data, string $answer): void
  {
    $cv_data['data']['name'] = $answer;
    $cv_data['step'] = 'professional_profile';
    $cv_data['message'] = 'Cuéntame brevemente tu perfil profesional';
  }

  private function handleProfessionalProfile(array &$cv_data, string $answer): void
  {
    $cv_data['data']['professional_profile'] = $answer;
    $cv_data['step'] = 'work_experience';
    $cv_data['message'] = 'Describe tu experiencia laboral. Formato: "Cargo;Empresa;Años;Meses;Días"';
  }

  private function handleWorkExperience(array &$cv_data, string $answer): void
  {
    [$job_title, $company, $years, $months, $days] = explode(';', $answer);

    $experience = [
      'job_title' => trim($job_title),
      'company_name' => trim($company),
      'duration' => [
        'years' => (int)$years,
        'months' => (int)$months,
        'days' => (int)$days
      ]
    ];

    $cv_data['data']['work_experience'][] = $experience;
    $cv_data['step'] = 'studies';
    $cv_data['message'] = '¿Cuáles son tus estudios? Formato: "Título;Institución;Años;Meses;Días"';
  }

  private function handleStudies(array &$cv_data, string $answer): void
  {
    [$degree, $institution, $years, $months, $days] = explode(';', $answer);

    $study = [
      'degree' => trim($degree),
      'institution' => trim($institution),
      'duration' => [
        'years' => (int)$years,
        'months' => (int)$months,
        'days' => (int)$days
      ]
    ];

    $cv_data['data']['studies'][] = $study;
    $cv_data['step'] = 'skills';
    $cv_data['message'] = 'Por último, dime tus habilidades principales separadas por comas';
  }

  private function handleSkills(array &$cv_data, string $answer): void
  {
    $cv_data['data']['skills'] = array_map('trim', explode(',', $answer));
    $cv_data['step'] = 'finished';
    $cv_data['message'] = '¡Perfecto! Ya tengo toda la información para tu CV 🎉';
  }
}
