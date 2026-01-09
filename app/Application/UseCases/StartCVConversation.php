<?php

class StartCVConversation
{
  public array $cv_data;

  public function startConversation()
  {
    $this->cv_data = [
      'step' => 'name',
      'data' => [
        'name' => null,
        'professional_profile' => null,
        'work_experience' => [],
        'studies' => [],
        'skills' => [],
      ],
    ];

    return [
      'message' => 'Hola 👋 Empezamos a crear tu CV. ¿Cuál es tu nombre completo?',
      'cv_data' => $this->cv_data,
    ];
  }
}
