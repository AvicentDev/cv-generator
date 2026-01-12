<?php

namespace App\Application\Contracts;

use App\Application\DTOs\CVConversationState;

interface HandleCVAnswerInterface
{
  public function handle(
    CVConversationState $state,
    string $answer
  ): CVConversationState;
}
