<?php

class GenerateCVText
{
  public function generate(array $cv_data): string
  {
    $data = $cv_data['data'];

    $cv = [];

    // Nombre
    $cv[] = strtoupper($data['name']);
    $cv[] = '';

    // Perfil profesional
    $cv[] = 'PERFIL PROFESIONAL';
    $cv[] = $data['professional_profile'];
    $cv[] = '';

    // Experiencia laboral
    $cv[] = 'EXPERIENCIA LABORAL';
    foreach ($data['work_experience'] as $experience) {
      $cv[] = '- ' . $experience;
    }
    $cv[] = '';

    // Estudios
    $cv[] = 'ESTUDIOS';
    foreach ($data['studies'] as $study) {
      $cv[] = '- ' . $study;
    }
    $cv[] = '';

    // Habilidades
    $cv[] = 'HABILIDADES';
    $cv[] = implode(' · ', $data['skills']);

    return implode(PHP_EOL, $cv);
  }
}
