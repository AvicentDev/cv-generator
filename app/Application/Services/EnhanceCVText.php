<?php

namespace App\Application\Services;

use OpenAI\Client;
use OpenAI\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Log;
use Throwable;

final class EnhanceCVText
{
  public function __construct(private Client $client) {}

  public function enhance(string $cvText): string
  {
    Log::info('[EnhanceCVText] Método enhance() llamado');

    $model = env('OPENAI_MODEL', 'gpt-4.1-mini');

    $prompt = <<<PROMPT
Corrige y mejora profesionalmente el siguiente CV.

Instrucciones:
- Corrige todos los errores ortográficos y gramaticales.
- Corrige mayúsculas y minúsculas.
- Mejora la redacción para que sea clara, profesional y natural.
- NO añadas información nueva.
- NO elimines información existente.
- Devuelve el resultado en **Markdown**, con títulos y listas claras.
- NO incluyas explicaciones ni comentarios adicionales.

CV ORIGINAL (contenido literal):
"""
$cvText
"""

Devuelve **solo** el texto final en Markdown.
PROMPT;

    $attempts = 0;
    $maxAttempts = 3;

    while ($attempts < $maxAttempts) {
      try {
        Log::info('[EnhanceCVText] Llamando a OpenAI', [
          'attempt' => $attempts + 1,
          'model'   => $model,
        ]);

        $response = $this->client->chat()->create([
          'model' => $model,
          'messages' => [
            [
              'role' => 'system',
              'content' => 'Eres un editor profesional de CVs y experto en formateo Markdown.'
            ],
            [
              'role' => 'user',
              'content' => $prompt
            ],
          ],
          'temperature' => 0.3,
          'max_tokens'  => 1200,
        ]);

        $result = trim($response->choices[0]->message->content);

        Log::info('[EnhanceCVText] Respuesta recibida de OpenAI', [
          'length' => strlen($result),
        ]);

        return $result;
      } catch (RateLimitException $e) {
        $attempts++;

        Log::warning('[EnhanceCVText] Rate limit alcanzado', [
          'attempt' => $attempts,
          'message' => $e->getMessage(),
        ]);

        sleep(2 * $attempts);
      } catch (Throwable $e) {
        Log::error('[EnhanceCVText] Error inesperado', [
          'exception' => $e,
        ]);

        return $cvText;
      }
    }

    Log::error('[EnhanceCVText] Máximo de intentos alcanzado, devolviendo texto original');

    return $cvText;
  }
}
