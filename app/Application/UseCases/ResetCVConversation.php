<?php
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
