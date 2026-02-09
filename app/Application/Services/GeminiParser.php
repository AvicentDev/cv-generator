<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class GeminiParser
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
        $this->model = env('GEMINI_MODEL', 'gemini-pro');
    }

    /**
     * Parsea experiencia laboral usando Gemini AI
     */
    public function parseWorkExperience(string $text): ?array
    {
        $prompt = <<<PROMPT
Eres un asistente experto en interpretar respuestas naturales sobre experiencia laboral. Analiza el siguiente texto y determina si contiene información válida de experiencia laboral.

Texto del usuario: "$text"

Tu tarea:
1. Determina si el usuario está describiendo experiencia laboral REAL (con cargo y empresa específicos)
2. Si el usuario indica que NO tiene experiencia (de cualquier forma: "nada", "ninguna", "no tengo", "sin experiencia", "todavía no", "aún no", respuestas vagas como "gf", "pues nada", etc.), devuelve null
3. Si falta algún dato (cargo o empresa), intenta inferirlo o pon "No especificado". NO devuelvas null a menos que sea claramente una respuesta negativa o sin sentido.
4. Si el usuario describe una experiencia válida, extrae los datos.

Formato de respuesta (JSON puro, sin markdown):
{
  "job_title": "cargo o puesto específico (o 'No especificado')",
  "company_name": "nombre de la empresa (o 'No especificado')",
  "duration": {
    "years": número_de_años,
    "months": número_de_meses,
    "days": 0
  }
}

Reglas de duración:
- Si no menciona duración → years=0, months=0
- Si dice "hace poco" o vago → years=0, months=0

Ejemplos:
- "Trabajé como desarrollador en Google por 2 años" → {"job_title":"Desarrollador","company_name":"Google","duration":{"years":2,"months":0,"days":0}}
- "Fui camarero" → {"job_title":"Camarero","company_name":"No especificado","duration":{"years":0,"months":0,"days":0}}
- "nada" → null
- "sin experiencia" → null
- "no" → null

Devuelve SOLO el JSON o null, sin explicaciones ni bloques de código markdown.
PROMPT;

        Log::info('Parsing work experience with Gemini', ['text' => $text]);
        return $this->callGemini($prompt);
    }

    /**
     * Parsea educación usando Gemini AI
     */
    public function parseEducation(string $text): ?array
    {
        $prompt = <<<PROMPT
Eres un asistente experto en interpretar respuestas naturales sobre formación académica. Analiza el siguiente texto y determina si contiene información válida de educación.

Texto del usuario: "$text"

Tu tarea:
1. Determina si el usuario está describiendo formación académica REAL (con título/grado e institución específicos)
2. Si el usuario indica que NO tiene formación (de cualquier forma: "nada", "ninguna", "no tengo", "sin estudios", "todavía no", "aún no", respuestas vagas, etc.), devuelve null
3. Si falta algún dato (título o institución), intenta inferirlo o pon "No especificado". NO devuelvas null si hay intención de describir estudios.
4. Si el usuario describe estudios válidos, extrae los datos.

Formato de respuesta (JSON puro, sin markdown):
{
  "degree": "título o grado académico (o 'No especificado')",
  "institution": "nombre de la institución (o 'No especificado')",
  "duration": {
    "years": número_de_años,
    "months": número_de_meses,
    "days": 0
  }
}

Reglas de duración:
- Si no menciona duración → years=0, months=0

Ejemplos:
- "Estudié Ingeniería en la Universidad de Madrid" → {"degree":"Ingeniería","institution":"Universidad de Madrid","duration":{"years":0,"months":0,"days":0}}
- "hice un curso de cocina" → {"degree":"Curso de Cocina","institution":"No especificado","duration":{"years":0,"months":0,"days":0}}
- "nada" → null
- "no" → null

Devuelve SOLO el JSON o null, sin explicaciones ni bloques de código markdown.
PROMPT;

        Log::info('Parsing education with Gemini', ['text' => $text]);
        return $this->callGemini($prompt);
    }

    /**
     * Parsea habilidades usando Gemini AI
     */
    public function parseSkills(string $text): array
    {
        $prompt = <<<PROMPT
Eres un asistente experto en identificar habilidades técnicas y profesionales. Analiza el siguiente texto y extrae TODAS las habilidades mencionadas.

Texto del usuario: "$text"

Tu tarea:
1. Identifica TODAS las habilidades técnicas, lenguajes de programación, frameworks, herramientas, metodologías, soft skills, etc.
2. Normaliza los nombres correctamente (ej: "react" → "React", "nodejs" → "Node.js", "python" → "Python")
3. Separa habilidades que estén unidas por "y", comas, puntos, etc.
4. Incluye tanto habilidades técnicas como blandas si las menciona
5. Si el texto es muy vago o no contiene habilidades claras, intenta inferir del contexto

Formato de respuesta (JSON puro, sin markdown):
{
  "skills": ["habilidad1", "habilidad2", "habilidad3"]
}

Ejemplos:
- "JavaScript, React y Node.js" → {"skills":["JavaScript","React","Node.js"]}
- "Sé programar en python y usar git" → {"skills":["Python","Git"]}
- "Trabajo en equipo, liderazgo, comunicación" → {"skills":["Trabajo en equipo","Liderazgo","Comunicación"]}
- "desarrollo web frontend" → {"skills":["Desarrollo web","Frontend"]}
- "nada" → {"skills":[]}
- "" → {"skills":[]}

Devuelve SOLO el JSON, sin explicaciones ni bloques de código markdown.
PROMPT;

        $result = $this->callGemini($prompt);

        if ($result && isset($result['skills']) && is_array($result['skills'])) {
            return $result['skills'];
        }

        // Fallback: separar por espacios y comas
        return array_values(array_filter(array_map('trim', preg_split('/[,\\s]+/', $text))));
    }

    /**
     * Llama a la API de Gemini
     */
    private function callGemini(string $prompt): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API key not configured');
            return null;
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 500,
                ]
            ]);

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$content) {
                Log::error('No content in Gemini response', ['data' => $data]);
                return null;
            }

            Log::info('Gemini response received', ['content' => $content]);

            // Limpiar markdown si existe
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*/', '', $content);
            $content = trim($content);

            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'content' => $content,
                    'error' => json_last_error_msg()
                ]);
                return null;
            }

            Log::info('Successfully parsed Gemini response', ['parsed' => $parsed]);
            return $parsed;

        } catch (\Exception $e) {
            Log::error('Error calling Gemini', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
