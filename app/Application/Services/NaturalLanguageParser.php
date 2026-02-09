<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class NaturalLanguageParser
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
        $this->model = config('services.openai.model') ?? env('OPENAI_MODEL', 'gpt-4o-mini');
    }


    /**
     * Parsea experiencia laboral desde texto natural
     *
     * @param string $text Texto en lenguaje natural
     * @return array|null Array con job_title, company_name, duration o null si falla
     */
    public function parseWorkExperience(string $text): ?array
    {
        $prompt = <<<PROMPT
Eres un asistente experto en interpretar respuestas naturales sobre experiencia laboral. Analiza el siguiente texto y determina si contiene información válida de experiencia laboral.

Texto del usuario: "$text"

Tu tarea:
1. Determina si el usuario está describiendo experiencia laboral REAL (con cargo y empresa específicos)
2. Si el usuario indica que NO tiene experiencia (de cualquier forma: "nada", "ninguna", "no tengo", "sin experiencia", "todavía no", "aún no", respuestas vagas como "gf", "pues nada", etc.), devuelve null
3. Si el usuario da información incompleta o muy vaga que no permite identificar un cargo Y una empresa, devuelve null
4. Solo si hay información clara de un trabajo real, extrae los datos

Formato de respuesta (JSON puro, sin markdown):
{
  "job_title": "cargo o puesto específico",
  "company_name": "nombre de la empresa",
  "duration": {
    "years": número_de_años,
    "months": número_de_meses,
    "days": 0
  }
}

Reglas de duración:
- Si dice "3 años y medio" o "3.5 años" → years=3, months=6
- Si dice "2 años" → years=2, months=0
- Si dice "6 meses" → years=0, months=6
- Si no menciona duración → years=0, months=0
- Si dice "desde 2020 hasta 2023" → calcula la diferencia

Ejemplos:
- "Trabajé como desarrollador en Google por 2 años" → {"job_title":"Desarrollador","company_name":"Google","duration":{"years":2,"months":0,"days":0}}
- "nada" → null
- "pues nada por ahora" → null
- "gf" → null
- "sin experiencia" → null
- "todavía no he trabajado" → null

Devuelve SOLO el JSON o null, sin explicaciones ni bloques de código markdown.
PROMPT;

        Log::info('Parsing work experience', ['text' => $text, 'api_key_set' => !empty($this->apiKey)]);
        return $this->callOpenAI($prompt);
    }

    /**
     * Parsea educación desde texto natural
     *
     * @param string $text Texto en lenguaje natural
     * @return array|null Array con degree, institution, duration o null si falla
     */
    public function parseEducation(string $text): ?array
    {
        $prompt = <<<PROMPT
Eres un asistente experto en interpretar respuestas naturales sobre formación académica. Analiza el siguiente texto y determina si contiene información válida de educación.

Texto del usuario: "$text"

Tu tarea:
1. Determina si el usuario está describiendo formación académica REAL (con título/grado e institución específicos)
2. Si el usuario indica que NO tiene formación (de cualquier forma: "nada", "ninguna", "no tengo", "sin estudios", "todavía no", "aún no", respuestas vagas, etc.), devuelve null
3. Si el usuario da información incompleta o muy vaga que no permite identificar un título Y una institución, devuelve null
4. Solo si hay información clara de estudios reales, extrae los datos

Formato de respuesta (JSON puro, sin markdown):
{
  "degree": "título o grado académico específico",
  "institution": "nombre de la institución educativa",
  "duration": {
    "years": número_de_años,
    "months": número_de_meses,
    "days": 0
  }
}

Reglas de duración:
- Si dice "4 años y medio" → years=4, months=6
- Si dice "3 años" → years=3, months=0
- Si dice "18 meses" → years=1, months=6
- Si no menciona duración → years=0, months=0
- Carreras típicas: Licenciatura=4 años, Maestría=2 años, Doctorado=4 años

Ejemplos:
- "Estudié Ingeniería en la Universidad de Madrid" → {"degree":"Ingeniería","institution":"Universidad de Madrid","duration":{"years":0,"months":0,"days":0}}
- "Licenciatura en Informática, UNAM, 4 años" → {"degree":"Licenciatura en Informática","institution":"UNAM","duration":{"years":4,"months":0,"days":0}}
- "nada" → null
- "ninguna" → null
- "sin estudios" → null
- "todavía no" → null

Devuelve SOLO el JSON o null, sin explicaciones ni bloques de código markdown.
PROMPT;

        return $this->callOpenAI($prompt);
    }

    /**
     * Parsea habilidades desde texto natural
     *
     * @param string $text Texto en lenguaje natural
     * @return array Array de habilidades
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

        $result = $this->callOpenAI($prompt);

        if ($result && isset($result['skills']) && is_array($result['skills'])) {
            return $result['skills'];
        }

        // Fallback: separar por comas y espacios
        return array_values(array_filter(array_map('trim', preg_split('/[,\s]+/', $text))));
    }

    /**
     * Llama a la API de OpenAI
     *
     * @param string $prompt
     * @return array|null
     */
    private function callOpenAI(string $prompt): ?array
    {
        try {
            Log::info('Calling OpenAI API', [
                'model' => $this->model,
                'api_key_length' => strlen($this->apiKey ?? ''),
                'has_api_key' => !empty($this->apiKey)
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Eres un asistente que extrae información estructurada de texto. Siempre respondes con JSON válido sin bloques de código markdown.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 500,
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

            Log::info('Successfully parsed OpenAI response', ['parsed' => $parsed]);
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
