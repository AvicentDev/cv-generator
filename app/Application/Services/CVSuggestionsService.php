<?php

namespace App\Application\Services;

final class CVSuggestionsService
{
    /**
     * Obtiene sugerencias contextuales basadas en el paso actual
     */
    public function getSuggestionsForStep(string $step): array
    {
        return match ($step) {
            'name' => [],
            'professional_profile' => [
                'Desarrollador Full Stack con 5 años de experiencia en tecnologías web modernas',
                'Ingeniero de Software especializado en arquitecturas backend escalables',
                'Diseñador UX/UI enfocado en crear experiencias digitales intuitivas',
            ],
            'work_experience' => [
                'Trabajé como Desarrollador Senior en TechCorp durante 3 años y medio',
                'Fui Ingeniero de Software en StartupXYZ por 2 años',
                'Desarrollador Full Stack en Acme Corp, 4 años',
            ],
            'studies' => [
                'Estudié Ingeniería en Sistemas en la Universidad Nacional durante 4 años',
                'Licenciatura en Informática en el Instituto Tecnológico, 3 años y medio',
                'Ingeniería de Software en la Universidad Politécnica por 5 años',
            ],
            'skills' => [
                'JavaScript React Node Python SQL Docker',
                'Java Spring Boot Kubernetes AWS MongoDB',
                'TypeScript Next.js PostgreSQL Redis Git',
            ],
            default => [],
        };
    }

    /**
     * Obtiene el número de paso actual (1-5)
     */
    public function getStepNumber(string $step): int
    {
        return match ($step) {
            'name' => 1,
            'professional_profile' => 2,
            'work_experience' => 3,
            'studies' => 4,
            'skills' => 5,
            'finished' => 5,
            default => 1,
        };
    }

    /**
     * Obtiene el total de pasos
     */
    public function getTotalSteps(): int
    {
        return 5;
    }

    /**
     * Obtiene requisitos de validación para un paso
     */
    public function getValidationRules(string $step): array
    {
        return match ($step) {
            'name' => [
                'min_length' => 3,
                'max_length' => 100,
            ],
            'professional_profile' => [
                'min_length' => 20,
                'max_length' => 500,
            ],
            'work_experience' => [
                'format' => 'Lenguaje natural',
                'example' => 'Trabajé como Desarrollador Senior en TechCorp durante 3 años y medio',
            ],
            'studies' => [
                'format' => 'Lenguaje natural',
                'example' => 'Estudié Ingeniería en Sistemas en la Universidad Nacional durante 4 años',
            ],
            'skills' => [
                'format' => 'Lenguaje natural o separadas por espacios',
                'example' => 'JavaScript React Node.js Python SQL Docker',
            ],
            default => [],
        };
    }
}
