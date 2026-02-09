<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Log;

final class RegexParser
{
    /**
     * Parsea experiencia laboral usando regex (sin IA)
     */
    public function parseWorkExperience(string $text): ?array
    {
        $text = trim($text);

        // Patrones para detectar cargo, empresa y duración
        $patterns = [
            // "Trabajé como X en Y durante Z"
            '/(?:trabaj[eé]|estuve)\s+como\s+([^,]+?)\s+en\s+([^,]+?)\s+(?:durante|por)\s+(.+)/iu',
            // "Fui X en Y por/durante Z" (sin "como")
            '/(?:fui|era)\s+([^,]+?)\s+en\s+([^,]+?)\s+(?:durante|por)\s+(.+)/iu',
            // "X en Y, Z tiempo"
            '/^([^,]+?)\s+en\s+([^,]+?),\s*(.+)/iu',
            // "X at Y for Z" (inglés)
            '/^([^,]+?)\s+at\s+([^,]+?)\s+for\s+(.+)/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $jobTitle = trim($matches[1]);
                $companyName = trim($matches[2]);
                $durationText = trim($matches[3]);

                $duration = $this->parseDuration($durationText);

                if ($duration) {
                    Log::info('Regex parser success (work)', [
                        'text' => $text,
                        'job_title' => $jobTitle,
                        'company_name' => $companyName,
                        'duration' => $duration
                    ]);

                    return [
                        'job_title' => $jobTitle,
                        'company_name' => $companyName,
                        'duration' => $duration
                    ];
                }
            }
        }

        Log::warning('Regex parser failed (work)', ['text' => $text]);
        return null;
    }

    /**
     * Parsea educación usando regex (sin IA)
     */
    public function parseEducation(string $text): ?array
    {
        $text = trim($text);

        // Patrones para detectar título, institución y duración
        $patterns = [
            // "Estudié X en Y durante/por Z"
            '/(?:estudi[eé]|curs[eé]|hice)\s+([^,]+?)\s+en\s+(?:la\s+)?([^,]+?)\s+(?:durante|por)\s+(.+)/iu',
            // "X en Y, Z tiempo"
            '/^([^,]+?)\s+en\s+(?:la\s+)?([^,]+?),\s*(.+)/iu',
            // "X at Y for Z" (inglés)
            '/^([^,]+?)\s+at\s+([^,]+?)\s+for\s+(.+)/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $degree = trim($matches[1]);
                $institution = trim($matches[2]);
                $durationText = trim($matches[3]);

                $duration = $this->parseDuration($durationText);

                if ($duration) {
                    Log::info('Regex parser success (education)', [
                        'text' => $text,
                        'degree' => $degree,
                        'institution' => $institution,
                        'duration' => $duration
                    ]);

                    return [
                        'degree' => $degree,
                        'institution' => $institution,
                        'duration' => $duration
                    ];
                }
            }
        }

        Log::warning('Regex parser failed (education)', ['text' => $text]);
        return null;
    }

    /**
     * Extrae duración de texto natural
     */
    private function parseDuration(string $text): ?array
    {
        $years = 0;
        $months = 0;
        $days = 0;

        // Patrones para años
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:años?|years?)/iu', $text, $matches)) {
            $yearValue = str_replace(',', '.', $matches[1]);
            $years = (int) floor((float) $yearValue);
            $decimal = (float) $yearValue - $years;
            $months = (int) round($decimal * 12);
        }

        // Patrones para meses (si no se calcularon de años decimales)
        if ($months === 0 && preg_match('/(\d+)\s*(?:meses?|months?)/iu', $text, $matches)) {
            $months = (int) $matches[1];
        }

        // Patrones para "medio" o "y medio"
        if (preg_match('/(?:y\s+)?(?:medio|half)/iu', $text)) {
            $months += 6;
        }

        // Normalizar meses a años si es necesario
        if ($months >= 12) {
            $years += (int) floor($months / 12);
            $months = $months % 12;
        }

        // Si no encontramos nada, retornar null
        if ($years === 0 && $months === 0 && $days === 0) {
            return null;
        }

        return [
            'years' => $years,
            'months' => $months,
            'days' => $days
        ];
    }
}
