<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class OpenAIParser
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    /**
     * Parsea experiencia laboral usando OpenAI
     */
    public function parseWorkExperience(string $text): ?array
    {
        $prompt = <<<PROMPT
Eres un experto en Recursos Humanos y redacción de CVs. Analiza el siguiente texto de un usuario sobre su experiencia laboral.

Texto del usuario: "$text"

Tu tarea:
1. Determina si el usuario describe una experiencia laboral real.
2. Si NO tiene experiencia o la respuesta es negativa ("no", "nada", "sin experiencia", etc.), devuelve null.
3. Si la experiencia es válida, extrae los datos Y MEJORA la descripción.
   - Genera una descripción profesional de las responsabilidades, usando verbos de acción y un lenguaje formal.
   - Si el usuario da pocos detalles, INFIERE responsabilidades estándar para ese puesto.

Formato de respuesta (JSON):
{
  "job_title": "Cargo o puesto (ej: 'Gerente de Ventas')",
  "company_name": "Nombre de la empresa (o 'Confidencial' si no se menciona)",
  "description": "Lista de 3-4 puntos clave o un párrafo profesional describiendo las responsabilidades y logros.",
  "duration": {
    "years": número,
    "months": número,
    "days": 0
  }
}

Ejemplos:
- Input: "fui camarero en el bar de pepe 2 meses"
  Output: {
    "job_title": "Camarero",
    "company_name": "Bar de Pepe",
    "description": "Atención al cliente y servicio de mesas en un entorno dinámico. Gestión de pedidos y mantenimiento de la limpieza del área de trabajo.",
    "duration": {"years":0,"months":2,"days":0}
  }

Responde SOLO con el JSON válido.
PROMPT;

        Log::info('Parsing work experience with OpenAI', ['text' => $text]);
        return $this->callOpenAI($prompt);
    }

    /**
     * Parsea educación usando OpenAI
     */
    public function parseEducation(string $text): ?array
    {
        $prompt = <<<PROMPT
Eres un experto en redacción de CVs. Analiza el texto sobre formación académica.

Texto del usuario: "$text"

Tu tarea:
1. Detectar si hay formación académica válida.
2. Si es negativa ("no tiene", "nada", etc.), devuelve null.
3. Extraer datos y formatear el título correctamente.

Formato de respuesta (JSON):
{
  "degree": "Título o Grado (ej: 'Ingeniería Informática')",
  "institution": "Institución educativa",
  "duration": {
    "years": número,
    "months": número,
    "days": 0
  }
}

Responde SOLO con el JSON válido.
PROMPT;

        Log::info('Parsing education with OpenAI', ['text' => $text]);
        return $this->callOpenAI($prompt);
    }

    /**
     * Parsea habilidades usando OpenAI
     */
    public function parseSkills(string $text): array
    {
        $prompt = <<<PROMPT
Eres un experto en perfiles profesionales. Extrae y categoriza las habilidades del texto.

Texto del usuario: "$text"

Tu tarea:
1. Identificar habilidades técnicas (hard skills) y blandas (soft skills).
2. Normalizar los nombres (ej: 'react js' -> 'React.js').
3. Si el usuario menciona un rol (ej: "soy fullstack"), infiere habilidades relacionadas (ej: "HTML", "CSS", "JavaScript", "Node.js", "DBs").

Formato de respuesta (JSON):
{
  "skills": ["Habilidad 1", "Habilidad 2", ...]
}

Responde SOLO con el JSON válido.
PROMPT;

        Log::info('Parsing skills with OpenAI', ['text' => $text]);
        $result = $this->callOpenAI($prompt);
        return $result['skills'] ?? [];
    }

    private function callOpenAI(string $prompt): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7, // Un poco más creativo para generar buenas descripciones
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                Log::error('No content in OpenAI response', ['data' => $data]);
                return null;
            }

            Log::info('OpenAI response received', ['content' => $content]);

            // Limpiar bloques de código markdown si el modelo los añade
            $content = preg_replace('/^```json\s*/m', '', $content);
            $content = preg_replace('/^```\s*/m', '', $content);
            $content = preg_replace('/```$/m', '', $content);
            $content = trim($content);

            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error from OpenAI', [
                    'error' => json_last_error_msg(),
                    'content' => $content
                ]);
                return null;
            }

            return $parsed;

        } catch (\Exception $e) {
            Log::error('Error calling OpenAI', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
