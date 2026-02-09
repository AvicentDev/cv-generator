<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiEnhancer
{
    private ?string $apiKey;
    private ?string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');

        if (empty($this->apiKey)) {
            Log::warning('Gemini API key not configured for GeminiEnhancer');
        }
    }

    /**
     * Mejora el perfil profesional del usuario
     */
    public function enhanceProfile(string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $prompt = <<<PROMPT
Eres un experto en redacción de perfiles profesionales para CVs. Tu tarea es mejorar el siguiente perfil profesional.

Texto del usuario: "$text"

INSTRUCCIONES:
1. Mejora la redacción haciéndola más profesional y convincente
2. Usa verbos de acción y lenguaje impactante
3. Mantén la esencia y la información del usuario
4. NO inventes información que no esté presente
5. Hazlo conciso pero potente (máximo 3-4 líneas)
6. Usa un tono profesional pero cercano

Devuelve SOLO el perfil mejorado, sin explicaciones ni formato markdown.
PROMPT;

        return $this->callGemini($prompt) ?? $text;
    }

    /**
     * Mejora la descripción de experiencia laboral
     */
    public function enhanceExperience(string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $prompt = <<<PROMPT
Eres un experto en redacción de experiencia laboral para CVs. Tu tarea es mejorar la siguiente descripción de experiencia.

Texto del usuario: "$text"

INSTRUCCIONES:
1. Mejora la redacción usando verbos de acción (desarrollé, implementé, optimicé, gestioné, lideré, etc.)
2. Haz que suene más profesional y orientado a logros
3. Mantén toda la información original del usuario
4. NO inventes responsabilidades o logros que no estén mencionados
5. Estructura la información de forma clara
6. Si hay múltiples experiencias, sepáralas claramente

Devuelve SOLO la experiencia mejorada, sin explicaciones ni formato markdown.
PROMPT;

        return $this->callGemini($prompt) ?? $text;
    }

    /**
     * Mejora la descripción de educación
     */
    public function enhanceEducation(string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $prompt = <<<PROMPT
Eres un experto en redacción de formación académica para CVs. Tu tarea es mejorar la siguiente descripción de educación.

Texto del usuario: "$text"

INSTRUCCIONES:
1. Mejora la redacción haciéndola más profesional
2. Mantén toda la información original del usuario
3. NO inventes títulos, instituciones o detalles que no estén presentes
4. Estructura la información de forma clara
5. Si hay múltiples estudios, sepáralos claramente

Devuelve SOLO la educación mejorada, sin explicaciones ni formato markdown.
PROMPT;

        return $this->callGemini($prompt) ?? $text;
    }

    /**
     * Genera un título profesional breve basado en el perfil
     */
    public function generateTitle(string $profile): string
    {
        if (empty(trim($profile))) {
            return '';
        }

        $prompt = <<<PROMPT
Eres un experto en RRHH. Tu tarea es generar un TÍTULO PROFESIONAL breve (máximo 5 palabras) basado en la siguiente descripción de perfil.

Perfil: "$profile"

INSTRUCCIONES:
1. Extrae el cargo principal o rol profesional (ej: "Senior Full Stack Developer", "Arquitecto de Software", "Diseñador UX/UI")
2. Hazlo conciso y profesional
3. NO incluyas puntos finales
4. NO incluyas palabras como "Título:", "Cargo:", etc.
5. Si no puedes determinar un título, devuelve "Profesional"

Devuelve SOLO el título.
PROMPT;

        return $this->callGemini($prompt) ?? 'Profesional';
    }

    /**
     * Llama a la API de Gemini para mejorar texto
     */
    private function callGemini(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API key not configured for enhancement');
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
                    'temperature' => 0.7,  // Balance entre creatividad y consistencia
                    'maxOutputTokens' => 500,
                ]
            ]);

            if (!$response->successful()) {
                Log::error('Gemini API error during enhancement', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$content) {
                Log::error('No content in Gemini enhancement response', ['data' => $data]);
                return null;
            }

            Log::info('Gemini enhancement successful', ['original_length' => strlen($prompt), 'enhanced_length' => strlen($content)]);

            return trim($content);
        } catch (\Exception $e) {
            Log::error('Exception during Gemini enhancement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
