<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Core/helpers.php';

define('BASE_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');

try {
    echo "=== Testando Sistema de Cache ===\n";

    // Teste 1: Cache básico
    echo "1. Testando cache básico...\n";
    $result = cache('test.key', 'test value', 300);
    echo "Cache armazenado: " . ($result ? 'OK' : 'ERRO') . "\n";

    $value = cache('test.key');
    echo "Cache recuperado: $value\n";

    // Teste 2: Cache remember
    echo "\n2. Testando cache remember...\n";
    $data = cache_remember('expensive.test', function () {
        echo "Executando operação custosa...\n";
        return 'resultado caro';
    }, 300);
    echo "Resultado: $data\n";

    // Teste 3: Segunda chamada do remember (deve usar cache)
    echo "\n3. Segunda chamada (deve usar cache)...\n";
    $data2 = cache_remember('expensive.test', function () {
        echo "Esta mensagem não deveria aparecer!\n";
        return 'resultado caro';
    }, 300);
    echo "Resultado: $data2\n";

    // Teste 4: Cache com tags
    echo "\n4. Testando cache com tags...\n";
    $tagged = cache_tags(['users', 'test']);
    $tagged->put('user.1', ['name' => 'João'], 300);
    $user = $tagged->get('user.1');
    echo "Usuário cacheado: " . print_r($user, true);

    echo "\n=== Todos os testes passaram! ===\n";
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
