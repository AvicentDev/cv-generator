<?php
final class HandleCVAnswer implements HandleCVAnswerInterface
{
  public function handle(
    CVConversationState $state,
    string $answer
  ): CVConversationState {

    return match ($state->step) {
      'name' => $this->handleName($state, $answer),
      'professional_profile' => $this->handleProfessionalProfile($state, $answer),
      'work_experience' => $this->handleWorkExperience($state, $answer),
      'studies' => $this->handleStudies($state, $answer),
      'skills' => $this->handleSkills($state, $answer),
      default => $state,
    };
  }

  private function handleName(CVConversationState $state, string $answer): CVConversationState
  {
    $state->draft->name = $answer;

    return new CVConversationState(
      step: 'professional_profile',
      draft: $state->draft,
      message: 'Cuéntame brevemente tu perfil profesional'
    );
  }

  private function handleProfessionalProfile(CVConversationState $state, string $answer): CVConversationState
  {
    $state->draft->professionalProfile = $answer;

    return new CVConversationState(
      step: 'work_experience',
      draft: $state->draft,
      message: 'Describe tu experiencia laboral. Formato: "Cargo;Empresa;Años;Meses;Días"'
    );
  }

  private function handleWorkExperience(CVConversationState $state, string $answer): CVConversationState
  {
    [$job, $company, $y, $m, $d] = array_map('trim', explode(';', $answer));

    $state->draft->workExperience[] = [
      'job_title' => $job,
      'company_name' => $company,
      'duration' => [
        'years' => (int)$y,
        'months' => (int)$m,
        'days' => (int)$d,
      ],
    ];

    return new CVConversationState(
      step: 'studies',
      draft: $state->draft,
      message: '¿Cuáles son tus estudios? Formato: "Título;Institución;Años;Meses;Días"'
    );
  }

  private function handleStudies(CVConversationState $state, string $answer): CVConversationState
  {
    [$degree, $institution, $y, $m, $d] = array_map('trim', explode(';', $answer));

    $state->draft->studies[] = [
      'degree' => $degree,
      'institution' => $institution,
      'duration' => [
        'years' => (int)$y,
        'months' => (int)$m,
        'days' => (int)$d,
      ],
    ];

    return new CVConversationState(
      step: 'skills',
      draft: $state->draft,
      message: 'Por último, dime tus habilidades principales separadas por comas'
    );
  }

  private function handleSkills(CVConversationState $state, string $answer): CVConversationState
  {
    $state->draft->skills = array_map('trim', explode(',', $answer));

    return new CVConversationState(
      step: 'finished',
      draft: $state->draft,
      message: '¡Perfecto! Ya tengo toda la información para tu CV 🎉'
    );
  }
}
