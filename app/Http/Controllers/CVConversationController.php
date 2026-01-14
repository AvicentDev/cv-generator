<?php

namespace App\Http\Controllers;

use App\Application\Services\EnhanceCVText;
use App\Application\UseCases\BuildCVFromConversation;
use App\Application\UseCases\GenerateCVText;
use App\Application\UseCases\HandleCVAnswer;
use App\Application\UseCases\StartCVConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class CVConversationController
{
  public function __construct(
    private StartCVConversation $startConversation,
    private HandleCVAnswer $handleCVAnswer,
    private BuildCVFromConversation $buildCV,
    private GenerateCVText $generateCVText,
    private EnhanceCVText $enhanceCVText
  ) {}

  public function start(Request $request)
  {
    $state = $this->startConversation->start();
    $conversationId = (string) Str::uuid();

    Cache::put(
      "cv_conversation:$conversationId",
      $state,
      now()->addMinutes(30)
    );

    return response()->json([
      'conversation_id' => $conversationId,
      'message'         => $state->message,
      'step'            => $state->step,
    ]);
  }

  public function answer(Request $request)
  {
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
    $state = Cache::get($cacheKey);

    if (!$state) {
      return response()->json([
        'finished' => true,
        'message'  => 'La conversación ya ha finalizado o ha expirado.',
      ]);
    }

    $newState = $this->handleCVAnswer->handle($state, $answer);

    // Conversación finalizada
    if ($newState->step === 'finished') {

      $enhancedCacheKey = "cv_enhanced:$conversationId";

      // Si ya existe el CV mejorado, lo devolvemos directamente
      if (Cache::has($enhancedCacheKey)) {
        return response()->json([
          'finished' => true,
          'message'  => $newState->message,
          'cv'       => Cache::get($enhancedCacheKey),
        ]);
      }

      // Construcción del CV
      $cv = $this->buildCV->build($newState->draft);
      $cvText = $this->generateCVText->generate($cv);

      // Cache para evitar múltiples llamadas a OpenAI
      $cvTextEnhanced = Cache::remember(
        $enhancedCacheKey,
        now()->addHours(1),
        fn() => $this->enhanceCVText->enhance($cvText)
      );

      // Guardamos estado final por poco tiempo
      Cache::put(
        $cacheKey,
        $newState,
        now()->addMinutes(5)
      );

      return response()->json([
        'finished' => true,
        'message'  => $newState->message,
        'cv'       => $cvTextEnhanced,
      ]);
    }

    // Conversación en curso
    Cache::put(
      $cacheKey,
      $newState,
      now()->addMinutes(30)
    );

    return response()->json([
      'finished' => false,
      'message'  => $newState->message,
      'step'     => $newState->step,
    ]);
  }
}
