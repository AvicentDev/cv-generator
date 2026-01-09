<?php

class GenerateCVText
{
  public function generate(array $cv_data): string
  {
    $data = $cv_data['data'];

    $cv = [];

    $cv[] = strtoupper($data['name']);
    $cv[] = '';

    $cv[] = 'PERFIL PROFESIONAL';
    $cv[] = $data['professional_profile'];
    $cv[] = '';

    $cv[] = 'EXPERIENCIA LABORAL';
    foreach ($data['work_experience'] as $experience) {
      $cv[] = '- ' . $experience;
    }
    $cv[] = '';

    $cv[] = 'ESTUDIOS';
    foreach ($data['studies'] as $study) {
      $cv[] = '- ' . $study;
    }
    $cv[] = '';

    $cv[] = 'HABILIDADES';
    $cv[] = implode(' · ', $data['skills']);

    return implode(PHP_EOL, $cv);
  }
}
