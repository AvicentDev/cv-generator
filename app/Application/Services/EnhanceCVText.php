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

    $model = config('services.openai.model', 'gpt-4o-mini');

    $prompt = <<<PROMPT
Eres un experto en redacción de CVs profesionales. Tu tarea es mejorar y optimizar el siguiente CV.

INSTRUCCIONES IMPORTANTES:
1. **Corrige ortografía y gramática**: Revisa todos los errores ortográficos, gramaticales y de puntuación.
2. **Mejora la redacción**: Haz que el lenguaje sea más profesional, claro y convincente. Usa verbos de acción (desarrollé, implementé, optimicé, gestioné, diseñé, etc.).
3. **Formato Markdown limpio y consistente**:
   - Usa ## para los títulos de sección (ej: ## NOMBRE, ## EXPERIENCIA PROFESIONAL)
   - NO uses negritas (**) en el texto del contenido, solo títulos simples
   - Usa viñetas (-) para listas de elementos
   - Separa secciones con una línea en blanco

4. **Estructura que DEBES seguir exactamente**:

## NOMBRE
[Nombre completo de la persona]

## CONTACTO
[Ciudad, País · Email · Teléfono · LinkedIn]

## PERFIL PROFESIONAL
[Descripción profesional en párrafo corrido, sin viñetas. Debe ser un texto coherente que resuma las capacidades y enfoque profesional]

## EXPERIENCIA PROFESIONAL
- [Título del puesto] en [Empresa] ([Duración ejemplo: 2 años])
Descripción de responsabilidades y logros en texto corrido.

## PROYECTOS PERSONALES (si aplica)
- [Nombre del Proyecto] - Descripción del proyecto y tecnologías utilizadas.

## EDUCACIÓN
- [Título/Grado] en [Institución] ([Duración ejemplo: 2 años])

## HABILIDADES TÉCNICAS
- Lenguajes de programación: [lista separada por comas]
- Frameworks & Librerías: [lista separada por comas]
- Bases de datos: [lista separada por comas]
- Herramientas: [lista separada por comas]

5. **Reglas estrictas de formato**:
   - NO uses asteriscos dobles (**) para negritas en el contenido
   - NO uses # (un solo hash) para títulos, siempre usa ##
   - Mantén el texto limpio y sin formato markdown innecesario
   - Separa bien las secciones con líneas en blanco

6. **NO inventes información**: Solo mejora lo que ya está presente.

CV ORIGINAL:
"""
$cvText
"""

Devuelve **únicamente** el CV mejorado siguiendo EXACTAMENTE la estructura indicada, sin explicaciones adicionales.
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
              'content' => 'Eres un consultor experto en recursos humanos y redacción profesional de CVs. Sigues exactamente las instrucciones de formato proporcionadas y generas contenido limpio sin formato markdown innecesario.'
            ],
            [
              'role' => 'user',
              'content' => $prompt
            ],
          ],
          'temperature' => 0.5,  // Balance entre creatividad y consistencia
          'max_tokens'  => 2000,
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
          'exception' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);

        return $cvText;
      }
    }

    Log::error('[EnhanceCVText] Máximo de intentos alcanzado, devolviendo texto original');

    return $cvText;
  }
}
