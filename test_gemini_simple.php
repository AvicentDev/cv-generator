<?php

// Test simple de Gemini sin Laravel
$apiKey = 'AIzaSyDvpsh2ZjgIqUcLhCIoHrEAZSErEdCVVd0';
$model = 'gemini-pro';

echo "=== Test Simple de Gemini ===\n\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Di solo "hola"']
            ]
        ]
    ]
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data),
        'timeout' => 30
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($url, false, $context);

if ($result === false) {
    echo "❌ ERROR: No se pudo conectar a Gemini\n";
    echo "Detalles del error:\n";
    print_r(error_get_last());
} else {
    $response = json_decode($result, true);

    if (isset($response['error'])) {
        echo "❌ ERROR de Gemini:\n";
        echo json_encode($response['error'], JSON_PRETTY_PRINT) . "\n";
    } else {
        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? 'Sin respuesta';
        echo "✅ ¡ÉXITO! Gemini respondió: $content\n";
    }
}
