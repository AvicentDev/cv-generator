<?php

/**
 * Script de prueba para verificar conexión con OpenAI
 *
 * Ejecutar con: php test_openai.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
$model = $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini';

echo "=== Test de Conexión con OpenAI ===\n\n";

if (!$apiKey) {
    echo "❌ ERROR: No se encontró OPENAI_API_KEY en .env\n";
    exit(1);
}

echo "✓ API Key encontrada (longitud: " . strlen($apiKey) . " caracteres)\n";
echo "✓ Modelo configurado: $model\n\n";

echo "Probando conexión con OpenAI...\n";

try {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
        'model' => $model,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Di solo "hola"'
            ]
        ],
        'max_tokens' => 10,
    ]);

    if ($response->successful()) {
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';

        echo "\n✅ ¡ÉXITO! OpenAI respondió correctamente\n";
        echo "Respuesta: $content\n\n";
        echo "La API de OpenAI está funcionando correctamente.\n";
        echo "Puedes cambiar a NaturalLanguageParser para máxima flexibilidad.\n";
    } else {
        $status = $response->status();
        $body = $response->body();

        echo "\n❌ ERROR: OpenAI devolvió código $status\n";
        echo "Respuesta: $body\n\n";

        if ($status === 401) {
            echo "⚠️  La API key es inválida o ha expirado.\n";
            echo "Necesitas obtener una nueva API key de OpenAI.\n";
        } elseif ($status === 429) {
            echo "⚠️  Has excedido el límite de requests.\n";
            echo "Espera unos minutos o actualiza tu plan de OpenAI.\n";
        }
    }
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Verifica tu conexión a internet y que no haya firewall bloqueando.\n";
}
