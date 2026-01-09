<?php
final class StartCVConversation
{
  public function start(): CVConversationState
  {
    return new CVConversationState(
      step: 'name',
      draft: new CVDraft(),
      message: 'Hola 👋 Empezamos a crear tu CV. ¿Cuál es tu nombre completo?'
    );
  }
}
