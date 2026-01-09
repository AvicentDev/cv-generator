<?php

class GenerateCVText
{
  public function generate(CVData $cv): string
  {
    $lines = [];


    $lines[] = strtoupper($cv->name->value());
    $lines[] = '';

    $lines[] = 'PERFIL PROFESIONAL';
    $lines[] = $cv->professional_profile->value();
    $lines[] = '';


    $lines[] = 'EXPERIENCIA LABORAL';

    foreach ($cv->work_experience->value() as $experience) {
      $lines[] = '- ' . $experience;
    }

    $lines[] = '';



    $lines[] = 'ESTUDIOS';

    foreach ($cv->studies->value() as $study) {
      $lines[] = '- ' . $study;
    }

    $lines[] = '';


    $lines[] = 'HABILIDADES';
    $lines[] = implode(
      ' · ',
      $cv->skills->value()
    );

    return implode(PHP_EOL, $lines);
  }
}
