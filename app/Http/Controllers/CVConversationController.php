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
      return response()->json([
        'error' => 'conversation_id requerido'
      ], 400);
    }

    $cacheKey = "cv_conversation:$conversationId";
    $state = Cache::get($cacheKey);

    if (!$state) {
      return response()->json([
        'finished' => true,
        'message' => 'La conversación ya ha finalizado o expirado.'
      ]);
    }

    $newState = $this->handleCVAnswer->handle(
      $state,
      $request->input('answer')
    );


    if ($newState->step === 'finished') {

      $cv = $this->build_cv->build($newState->draft);
      $cvText = $this->generate_cvtext->generate($cv);

      Cache::put(
        $cacheKey,
        $newState,
        now()->addMinutes(5)
      );

      return response()->json([
        'finished' => true,
        'message'  => $newState->message,
        'cv'       => $cvText,
      ]);
    }

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
