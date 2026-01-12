<?php

namespace App\Application\UseCases;

use App\Application\DTOs\CVConversationState;
use App\Application\DTOs\CVDraft;

final class ResetCVConversation
{
  public function reset(): CVConversationState
  {
    return new CVConversationState(
      step: 'name',
      draft: new CVDraft(),
      message: '🔄 Empezamos de nuevo. ¿Cuál es tu nombre completo?'
    );
  }
}
