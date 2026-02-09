<?php

/**
 * Script de prueba para verificar conexión con Gemini AI
 *
 * Ejecutar con: php test_gemini.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['GEMINI_API_KEY'] ?? null;
$model = $_ENV['GEMINI_MODEL'] ?? 'gemini-1.5-flash';

echo "=== Test de Conexión con Gemini AI ===\n\n";

if (!$apiKey) {
    echo "❌ ERROR: No se encontró GEMINI_API_KEY en .env\n";
    echo "Agrega tu API key de Gemini al archivo .env\n";
    exit(1);
}

echo "✓ API Key encontrada (longitud: " . strlen($apiKey) . " caracteres)\n";
echo "✓ Modelo configurado: $model\n\n";

echo "Probando conexión con Gemini...\n";

try {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $response = Http::timeout(30)->post($url, [
        'contents' => [
            [
                'parts' => [
                    ['text' => 'Di solo "hola"']
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.2,
            'maxOutputTokens' => 10,
        ]
    ]);

    if ($response->successful()) {
        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        echo "\n✅ ¡ÉXITO! Gemini respondió correctamente\n";
        echo "Respuesta: $content\n\n";
        echo "La API de Gemini está funcionando correctamente.\n";
        echo "Ahora puedes usar GeminiParser para máxima flexibilidad.\n";
    } else {
        $status = $response->status();
        $body = $response->body();

        echo "\n❌ ERROR: Gemini devolvió código $status\n";
        echo "Respuesta: $body\n\n";

        if ($status === 400) {
            echo "⚠️  La API key es inválida o el modelo no existe.\n";
            echo "Verifica tu API key en Google AI Studio.\n";
        } elseif ($status === 429) {
            echo "⚠️  Has excedido el límite de requests.\n";
            echo "Espera unos minutos.\n";
        }
    }
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Verifica tu conexión a internet.\n";
}
