<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class OpenAIEnhancer
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
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

        return $this->callOpenAI($prompt) ?? $text;
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

Texto:
"$text"

INSTRUCCIONES:
1. Revisa el texto y asegúrate de que tenga un tono profesional.
2. Si el texto ya contiene descripciones detalladas (porque fue procesado previamente), MANTENLAS y solo corrige gramática o flujo si es necesario.
3. Si el texto es esquemático, expándelo ligeramente usando verbos de acción.
4. NO elimines detalles importantes.
5. NO inventes responsabilidades.

Devuelve SOLO la experiencia mejorada/revisada.
PROMPT;

        return $this->callOpenAI($prompt) ?? $text;
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

Texto: "$text"

INSTRUCCIONES:
1. Mejora la redacción haciéndola más profesional
2. Mantén toda la información original
3. NO inventes títulos o instituciones

Devuelve SOLO la educación mejorada.
PROMPT;

        return $this->callOpenAI($prompt) ?? $text;
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
1. Extrae el cargo principal o rol profesional (ej: "Senior Full Stack Developer")
2. Hazlo conciso y profesional
3. NO incluyas puntos finales ni etiquetas como "Título:"
4. Si no puedes determinar un título, devuelve "Profesional"

Devuelve SOLO el título.
PROMPT;

        return $this->callOpenAI($prompt) ?? 'Profesional';
    }

    private function callOpenAI(string $prompt): ?string
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
                'temperature' => 0.5,
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
                return null;
            }

            // Limpiar markdown
            $content = preg_replace('/^```\w*\s*/m', '', $content);
            $content = preg_replace('/```$/m', '', $content);
            $content = preg_replace('/^"|"$|^\\\'|\\\'$/', '', trim($content)); // Quitar comillas extra si las pone

            return trim($content);

        } catch (\Exception $e) {
            Log::error('Error calling OpenAI', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
