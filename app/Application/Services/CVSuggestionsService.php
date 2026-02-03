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
                'Desarrollador Senior;TechCorp;3;6;0',
                'Ingeniero de Software;StartupXYZ;2;0;0',
            ],
            'studies' => [
                'Ingeniería en Sistemas;Universidad Nacional;4;0;0',
                'Licenciatura en Informática;Instituto Tecnológico;3;6;0',
            ],
            'skills' => [
                'JavaScript, TypeScript, React, Node.js, Python, SQL',
                'Java, Spring Boot, Docker, Kubernetes, AWS, MongoDB',
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
                'min_length' => 50,
                'max_length' => 500,
            ],
            'work_experience' => [
                'format' => 'Cargo;Empresa;Años;Meses;Días',
                'example' => 'Desarrollador Senior;TechCorp;3;6;0',
            ],
            'studies' => [
                'format' => 'Título;Institución;Años;Meses;Días',
                'example' => 'Ingeniería en Sistemas;Universidad Nacional;4;0;0',
            ],
            'skills' => [
                'format' => 'Separadas por comas',
                'example' => 'JavaScript, React, Node.js, Python',
            ],
            default => [],
        };
    }
}
