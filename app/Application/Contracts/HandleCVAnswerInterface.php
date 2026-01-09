<?php

interface HandleCVAnswerInterface
{
  public function handle(
    CVConversationState $state,
    string $answer
  ): CVConversationState;
}
