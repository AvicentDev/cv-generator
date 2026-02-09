<?php

namespace App\Http\Controllers;

use App\Application\Services\EnhanceCVText;
use App\Application\Services\OpenAIEnhancer;
use App\Application\Services\CVSuggestionsService;
use App\Application\UseCases\BuildCVFromConversation;
use App\Application\UseCases\GenerateCVText;
use App\Application\UseCases\HandleCVAnswer;
use App\Application\UseCases\StartCVConversation;
use App\Application\DTOs\CVConversationState;
use App\Application\DTOs\CVDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

final class CVConversationController
{
  public function __construct(
    private StartCVConversation $startConversation,
    private HandleCVAnswer $handleCVAnswer,
    private BuildCVFromConversation $buildCV,
    private GenerateCVText $generateCVText,
    private EnhanceCVText $enhanceCVText,
    private OpenAIEnhancer $enhancer
  ) {}

  public function start(Request $request) {
    // ... (method body omitted as it's unchanged until answer method)
    try {
      $state = $this->startConversation->start();
      $conversationId = (string) Str::uuid();

      // Serializar el estado a array para guardar en cache
      $stateData = [
        'step' => $state->step,
        'draft' => [
          'name' => $state->draft->name,
          'professionalProfile' => $state->draft->professionalProfile,
          'workExperience' => $state->draft->workExperience,
          'studies' => $state->draft->studies,
          'skills' => $state->draft->skills,
        ],
        'message' => $state->message,
      ];

      Cache::put(
        "cv_conversation:$conversationId",
        $stateData,
        now()->addMinutes(30)
      );

      $suggestionsService = new CVSuggestionsService();

      return response()->json([
        'conversation_id' => $conversationId,
        'message' => $state->message,
        'step' => $state->step,
        'step_number' => $suggestionsService->getStepNumber($state->step),
        'total_steps' => $suggestionsService->getTotalSteps(),
        'suggestions' => $suggestionsService->getSuggestionsForStep($state->step),
        'validation' => $suggestionsService->getValidationRules($state->step),
      ]);
    } catch (\Exception $e) {
      Log::error('Error en start: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
      ]);

      return response()->json([
        'error' => 'Error al iniciar conversación',
        'details' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function answer(Request $request)
  {
    try {
      $conversationId = (string) $request->input('conversation_id');

      if ($conversationId === '') {
        return response()->json([
          'error' => 'conversation_id requerido'
        ], 400);
      }

      $answer = trim((string) $request->input('answer'));

      if ($answer === '') {
        return response()->json([
          'error' => 'answer requerido'
        ], 400);
      }

      $cacheKey = "cv_conversation:$conversationId";
      $stateData = Cache::get($cacheKey);

      if (!$stateData) {
        return response()->json([
          'finished' => true,
          'message' => 'La conversación ya ha finalizado o ha expirado.',
        ]);
      }

      // Deserializar el estado desde array
      $state = $this->deserializeState($stateData);

      $newState = $this->handleCVAnswer->handle($state, $answer);

      // Conversación finalizada
      if ($newState->step === 'finished') {

        // Construcción del CV
        $cv = $this->buildCV->build($newState->draft);
        $cvText = $this->generateCVText->generate($cv);

        // Guardamos estado final por poco tiempo
        Cache::put(
          $cacheKey,
          $this->serializeState($newState),
          now()->addMinutes(5)
        );

        // Formatear experiencia laboral
        $formattedExperience = [];
        foreach ($newState->draft->workExperience ?? [] as $exp) {
          $jobTitle = $exp['job_title'] ?? '';
          $company = $exp['company_name'] ?? '';
          $description = $exp['description'] ?? '';

          $durationText = '';
          if (isset($exp['duration'])) {
             $years = $exp['duration']['years'] ?? 0;
             $months = $exp['duration']['months'] ?? 0;
             if ($years > 0 || $months > 0) {
                 $parts = [];
                 if ($years > 0) $parts[] = "$years " . ($years == 1 ? 'año' : 'años');
                 if ($months > 0) $parts[] = "$months " . ($months == 1 ? 'mes' : 'meses');
                 $durationText = implode(' y ', $parts);
             }
          }

          $item = "";
          if ($jobTitle === 'Experiencia Laboral' || $jobTitle === 'Experiencia') {
              $item = $company . ($durationText ? " ($durationText)" : '');
          } else {
              $item = "$jobTitle - $company" . ($durationText ? " ($durationText)" : '');
          }

          if ($description) {
              $item .= "\n" . $description;
          }

          $formattedExperience[] = $item;
        }

        $formattedStudies = [];
        foreach ($newState->draft->studies ?? [] as $stu) {
             $degree = $stu['degree'] ?? '';
             $institution = $stu['institution'] ?? '';

             $durationText = '';
             if (isset($stu['duration'])) {
                $years = $stu['duration']['years'] ?? 0;
                $months = $stu['duration']['months'] ?? 0;
                if ($years > 0 || $months > 0) {
                    $parts = [];
                    if ($years > 0) $parts[] = "$years " . ($years == 1 ? 'año' : 'años');
                    if ($months > 0) $parts[] = "$months " . ($months == 1 ? 'mes' : 'meses');
                    $durationText = implode(' y ', $parts);
                }
             }

             if ($degree === 'Formación' || $degree === 'Estudios') {
                 $formattedStudies[] = $institution . ($durationText ? " ($durationText)" : '');
             } else {
                 $formattedStudies[] = "$degree - $institution" . ($durationText ? " ($durationText)" : '');
             }
        }

        $perfilMejorado = $newState->draft->professionalProfile
          ? $this->enhancer->enhanceProfile($newState->draft->professionalProfile)
          : '';

        $experienciaMejorada = !empty($formattedExperience)
          ? $this->enhancer->enhanceExperience(implode("\n\n", $formattedExperience))
          : '';

        $educacionMejorada = !empty($formattedStudies)
          ? $this->enhancer->enhanceEducation(implode("\n\n", $formattedStudies))
          : '';

        // Generar título basado en el perfil mejorado
        $tituloGenerado = $perfilMejorado
          ? $this->enhancer->generateTitle($perfilMejorado)
          : '';

        // Formatear nombre (capitalizar)
        $nombreFormateado = ucwords(strtolower($newState->draft->name ?? ''));

        return response()->json([
          'finished' => true,
          'message' => $newState->message,
          'cv' => [
            'nombre' => $nombreFormateado,
            'titulo' => $tituloGenerado,
            'perfil' => $perfilMejorado,
            'experiencia' => $experienciaMejorada,
            'educacion' => $educacionMejorada,
            'habilidades' => is_array($newState->draft->skills)
              ? implode(', ', $newState->draft->skills)
              : ($newState->draft->skills ?? ''),
          ],
        ]);
      }

      // Conversación en curso
      Cache::put(
        $cacheKey,
        $this->serializeState($newState),
        now()->addMinutes(30)
      );

      $suggestionsService = new CVSuggestionsService();

      return response()->json([
        'finished' => false,
        'message' => $newState->message,
        'step' => $newState->step,
        'step_number' => $suggestionsService->getStepNumber($newState->step),
        'total_steps' => $suggestionsService->getTotalSteps(),
        'suggestions' => $suggestionsService->getSuggestionsForStep($newState->step),
        'validation' => $suggestionsService->getValidationRules($newState->step),
      ]);
    } catch (\Exception $e) {
      Log::error('Error en answer: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'conversation_id' => $conversationId ?? null,
        'answer' => $answer ?? null,
      ]);

      return response()->json([
        'error' => 'Error al procesar respuesta',
        'details' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  private function serializeState(CVConversationState $state): array
  {
    return [
      'step' => $state->step,
      'draft' => [
        'name' => $state->draft->name,
        'professionalProfile' => $state->draft->professionalProfile,
        'workExperience' => $state->draft->workExperience,
        'studies' => $state->draft->studies,
        'skills' => $state->draft->skills,
      ],
      'message' => $state->message,
    ];
  }

  private function deserializeState(array $data): CVConversationState
  {
    $draft = new CVDraft(
      name: $data['draft']['name'] ?? null,
      professionalProfile: $data['draft']['professionalProfile'] ?? null,
      workExperience: $data['draft']['workExperience'] ?? [],
      studies: $data['draft']['studies'] ?? [],
      skills: $data['draft']['skills'] ?? []
    );

    return new CVConversationState(
      step: $data['step'],
      draft: $draft,
      message: $data['message']
    );
  }

  private function formatStudies(array $studies): string
  {
    if (empty($studies)) {
      return '';
    }

    $formatted = [];
    foreach ($studies as $study) {
      $degree = $study['degree'] ?? '';
      $institution = $study['institution'] ?? '';
      $duration = $study['duration'] ?? [];

      $years = $duration['years'] ?? 0;
      $months = $duration['months'] ?? 0;

      $durationText = '';
      if ($years > 0) {
        $durationText .= "$years año" . ($years > 1 ? 's' : '');
      }
      if ($months > 0) {
        if ($durationText) $durationText .= ' y ';
        $durationText .= "$months mes" . ($months > 1 ? 'es' : '');
      }

      $formatted[] = "$degree - $institution" . ($durationText ? " ($durationText)" : '');
    }

    return implode("\n", $formatted);
  }
}
