<?php

namespace App\Http\Controllers;

use BuildCVFromConversation;
use GenerateCVText;
use HandleCVAnswer;
use Illuminate\Http\Request;
use StartCVConversation;

final class CVConversationController
{
  public function __construct(private StartCVConversation $startConversation, private HandleCVAnswer $handleCVAnswer, private BuildCVFromConversation $build_cv, private GenerateCVText $generate_cvtext) {}
  public function start(Request $request)
  {
    $state = $this->startConversation->start();
    $request->session()->put('cv_state', $state);
    return response()->json(['message' => $state->message, 'step' => $state->step,]);
  }
  public function answer(Request $request)
  {
    $state = $request->session()->get('cv_state');
    if (!$state) {
      return response()->json(['error' => 'La conversación no ha sido iniciada'], 400);
    }
    $newState = $this->handleCVAnswer->handle($state, $request->input('answer'));
    $request->session()->put('cv_state', $newState);
    if ($newState->step === 'finished') {
      $cv = $this->build_cv->build($newState->draft);
      $cvText = $this->generate_cvtext->generate($cv);
      $request->session()->forget('cv_state');
      return response()->json(['finished' => true, 'message' => $newState->message, 'cv' => $cvText,]);
    }
    return response()->json(['message' => $newState->message, 'step' => $newState->step,]);
  }
}
