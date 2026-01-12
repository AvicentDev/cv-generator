<?php

namespace App\Http\Controllers;

use App\Application\UseCases\BuildCVFromConversation;
use App\Application\UseCases\GenerateCVText;
use App\Application\UseCases\HandleCVAnswer;
use App\Application\UseCases\StartCVConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class CVConversationController
{
  public function __construct(private StartCVConversation $startConversation, private HandleCVAnswer $handleCVAnswer, private BuildCVFromConversation $build_cv, private GenerateCVText $generate_cvtext) {}
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
      'message' => $state->message,
      'step' => $state->step,
    ]);
  }


  public function answer(Request $request)
  {
    $conversationId = $request->input('conversation_id');

    if (!$conversationId) {
      return response()->json(['error' => 'conversation_id requerido'], 400);
    }

    $state = Cache::get("cv_conversation:$conversationId");

    if (!$state) {
      return response()->json(['error' => 'Conversación no encontrada o expirada'], 404);
    }

    $newState = $this->handleCVAnswer->handle(
      $state,
      $request->input('answer')
    );

    if ($newState->step === 'finished') {
      Cache::forget("cv_conversation:$conversationId");

      $cv = $this->build_cv->build($newState->draft);
      $cvText = $this->generate_cvtext->generate($cv);

      return response()->json([
        'finished' => true,
        'message' => $newState->message,
        'cv' => $cvText,
      ]);
    }

    Cache::put(
      "cv_conversation:$conversationId",
      $newState,
      now()->addMinutes(30)
    );

    return response()->json([
      'message' => $newState->message,
      'step' => $newState->step,
    ]);
  }
}
