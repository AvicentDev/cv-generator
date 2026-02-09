<?php

namespace App\Application\UseCases;

use App\Application\DTOs\CVConversationState;
use App\Application\Contracts\HandleCVAnswerInterface;
use App\Application\Services\OpenAIParser;

final class HandleCVAnswer implements HandleCVAnswerInterface
{
  private OpenAIParser $parser;

  public function __construct()
  {
    $this->parser = new OpenAIParser();
  }

  public function handle(
    CVConversationState $state,
    string $answer
  ): CVConversationState {

    if (
      $state->step === 'work_experience'
      && strtolower(trim($answer)) === 'no'
    ) {
      return new CVConversationState(
        step: 'studies',
        draft: $state->draft,
        message: '¿Cuál es tu formación académica?'
      );
    }

    if (
      $state->step === 'studies'
      && strtolower(trim($answer)) === 'no'
    ) {
      return new CVConversationState(
        step: 'skills',
        draft: $state->draft,
        message: 'Por último, ¿cuáles son tus habilidades técnicas?'
      );
    }

    return match ($state->step) {
      'name' => $this->handleName($state, $answer),
      'professional_profile' => $this->handleProfessionalProfile($state, $answer),
      'work_experience' => $this->handleWorkExperience($state, $answer),
      'studies' => $this->handleStudies($state, $answer),
      'skills' => $this->handleSkills($state, $answer),
      default => $state,
    };
  }

  private function handleName(
    CVConversationState $state,
    string $answer
  ): CVConversationState {
    $state->draft->name = trim($answer);

    return new CVConversationState(
      step: 'professional_profile',
      draft: $state->draft,
      message: 'Cuéntame brevemente sobre tu perfil profesional'
    );
  }

  private function handleProfessionalProfile(
    CVConversationState $state,
    string $answer
  ): CVConversationState {
    $state->draft->professionalProfile = trim($answer);

    return new CVConversationState(
      step: 'work_experience',
      draft: $state->draft,
      message: 'Cuéntame sobre tu experiencia laboral'
    );
  }

  private function handleWorkExperience(
    CVConversationState $state,
    string $answer
  ): CVConversationState {

    // Intentar parseo con IA - la IA es lo suficientemente inteligente para detectar
    // si el usuario tiene experiencia real o no
    $parsed = $this->parser->parseWorkExperience($answer);

    // Si la IA devolvió datos válidos, significa que hay experiencia laboral real
    if ($parsed && isset($parsed['job_title'])) {
      $state->draft->workExperience[] = [
        'job_title' => $parsed['job_title'] ?? 'No especificado',
        'company_name' => $parsed['company_name'] ?? 'No especificado',
        'description' => $parsed['description'] ?? null,
        'duration' => [
          'years' => $parsed['duration']['years'] ?? 0,
          'months' => $parsed['duration']['months'] ?? 0,
          'days' => $parsed['duration']['days'] ?? 0,
        ],
      ];

      $jobTitle = $parsed['job_title'] ?? 'Experiencia';
      $company = $parsed['company_name'] ?? '';

      return new CVConversationState(
        step: 'work_experience',
        draft: $state->draft,
        message: "✅ Entendido: $jobTitle $company. ¿Quieres añadir otra experiencia? Si no, escribe \"no\""
      );
    }

    // Fallback: Si no se pudo parsear pero NO es una respuesta negativa, guardamos el texto crudo
    if (!$this->isNegativeAnswer($answer)) {
         $state->draft->workExperience[] = [
            'job_title' => 'Experiencia Laboral',
            'company_name' => $answer, // Guardamos el texto original aquí
            'duration' => ['years' => 0, 'months' => 0, 'days' => 0],
          ];

          return new CVConversationState(
            step: 'work_experience',
            draft: $state->draft,
            message: "✅ Anotado. ¿Quieres añadir otra experiencia? Si no, escribe \"no\""
          );
    }

    // Si es negativa o no se pudo procesar
    return new CVConversationState(
      step: 'studies',
      draft: $state->draft,
      message: 'Entendido. ¿Cuál es tu formación académica?'
    );
  }

  private function handleStudies(
    CVConversationState $state,
    string $answer
  ): CVConversationState {

    // Intentar parseo con IA - la IA detecta si hay formación académica real
    $parsed = $this->parser->parseEducation($answer);

    // Si la IA devolvió datos válidos, significa que hay formación académica real
    if ($parsed && isset($parsed['degree'])) {
      $state->draft->studies[] = [
        'degree' => $parsed['degree'] ?? 'Estudios',
        'institution' => $parsed['institution'] ?? 'No especificada',
        'duration' => [
          'years' => $parsed['duration']['years'] ?? 0,
          'months' => $parsed['duration']['months'] ?? 0,
          'days' => $parsed['duration']['days'] ?? 0,
        ],
      ];

      $degree = $parsed['degree'] ?? 'Estudios';
      $institution = $parsed['institution'] ?? '';

      return new CVConversationState(
        step: 'skills',
        draft: $state->draft,
        message: "✅ Perfecto: $degree en $institution. Por último, ¿cuáles son tus habilidades técnicas?"
      );
    }

    // Fallback: Si no se pudo parsear pero NO es una respuesta negativa
    if (!$this->isNegativeAnswer($answer)) {
         $state->draft->studies[] = [
            'degree' => 'Formación',
            'institution' => $answer, // Guardamos el texto original aquí
            'duration' => ['years' => 0, 'months' => 0, 'days' => 0],
          ];

          return new CVConversationState(
            step: 'skills',
            draft: $state->draft,
            message: "✅ Perfecto. Por último, ¿cuáles son tus habilidades técnicas?"
          );
    }

    // Si la IA devolvió null, el usuario no tiene formación o dio una respuesta vaga
    // Avanzamos al siguiente paso
    return new CVConversationState(
      step: 'skills',
      draft: $state->draft,
      message: 'Entendido. Por último, ¿cuáles son tus habilidades técnicas?'
    );
  }

  private function isNegativeAnswer(string $text): bool
  {
      $normalized = strtolower(trim($text));
      $negatives = ['no', 'nada', 'ninguna', 'ninguno', 'sin experiencia', 'sin estudios', 'paso', 'siguiente', 'saltar'];

      if (in_array($normalized, $negatives)) return true;
      if (strlen($normalized) < 3) return true; // Respuestas muy cortas como "x" o "."

      return false;
  }

  private function handleSkills(
    CVConversationState $state,
    string $answer
  ): CVConversationState {
    // Intentar parseo inteligente con IA
    $skills = $this->parser->parseSkills($answer);

    if (!empty($skills)) {
      $state->draft->skills = $skills;
    } else {
      // Fallback: separar por comas
      $state->draft->skills = array_map('trim', explode(',', $answer));
    }

    return new CVConversationState(
      step: 'finished',
      draft: $state->draft,
      message: '¡Perfecto! Ya tengo toda la información para tu CV 🎉'
    );
  }


  private function explodeOrFail(string $answer, int $expected): ?array
  {
    $parts = array_map('trim', explode(';', $answer));
    return count($parts) === $expected ? $parts : null;
  }
}
