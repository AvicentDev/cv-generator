<?php
final class CVConversationState
{
  public function __construct(
    public string $step,
    public CVDraft $draft,
    public string $message
  ) {}
}
