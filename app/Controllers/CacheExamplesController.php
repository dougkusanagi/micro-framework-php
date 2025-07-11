<?php

namespace App\Controllers;

use GuepardoSys\Core\Cache\Cache;

/**
 * Cache Examples Controller
 * 
 * Demonstra diferentes formas de usar o cache com interface Laravel EXATA
 */
class CacheExamplesController extends BaseController
{
    /**
     * Exemplo 1: Cache básico com helper (Interface Laravel EXATA)
     */
    public function basicCache(): string
    {
        // Usando helper function - interface Laravel exata
        $expensiveData = cache_remember('expensive.calculation', 300, function () {
            // Simula operação custosa
            sleep(1);
            return [
                'result' => rand(1, 1000),
                'calculated_at' => date('Y-m-d H:i:s'),
                'processing_time' => '1 second'
            ];
        });

        return $this->view('examples.basic-cache', [
            'title' => 'Cache Básico - Interface Laravel',
            'data' => $expensiveData
        ]);
    }

    /**
     * Exemplo 2: Cache com tags (interface Laravel EXATA)
     */
    public function taggedCache(): string
    {
        // Cache com tags - exatamente como no Laravel
        $userData = Cache::tags(['users', 'profiles'])->remember('user.1.profile', 1800, function () {
            return [
                'id' => 1,
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'preferences' => [
                    'theme' => 'dark',
                    'language' => 'pt-BR'
                ]
            ];
        });

        // Cache de estatísticas do usuário
        $userStats = Cache::tags(['users', 'statistics'])->remember('user.1.stats', 3600, function () {
            return [
                'total_posts' => rand(10, 100),
                'total_comments' => rand(50, 500),
                'reputation' => rand(100, 1000),
                'joined_date' => '2024-01-15'
            ];
        });

        return $this->view('examples.tagged-cache', [
            'title' => 'Cache com Tags - Interface Laravel',
            'userData' => $userData,
            'userStats' => $userStats
        ]);
    }

    /**
     * Exemplo 3: Cache de consultas de banco (interface Laravel EXATA)
     */
    public function databaseCache(): string
    {
        // Cache de consulta - interface Laravel
        $users = Cache::remember('users.all', 600, function () {
            return [
                ['id' => 1, 'name' => 'João', 'email' => 'joao@test.com'],
                ['id' => 2, 'name' => 'Maria', 'email' => 'maria@test.com'],
                ['id' => 3, 'name' => 'Pedro', 'email' => 'pedro@test.com']
            ];
        });

        // Cache de contagem
        $userCount = Cache::remember('users.count', 1800, function () use ($users) {
            return count($users);
        });

        return $this->view('examples.database-cache', [
            'title' => 'Cache de Banco - Interface Laravel',
            'users' => $users,
            'userCount' => $userCount
        ]);
    }

    /**
     * Exemplo 4: Cache incremental e múltiplas operações (interface Laravel EXATA)
     */
    public function incrementalCache(): string
    {
        // Incrementa contador - interface Laravel
        $pageViews = Cache::increment('page.views.cache-examples');
        $apiCalls = Cache::increment('api.calls.today', 1);

        // Cache múltiplo - interface Laravel
        Cache::putMany([
            'last.visit.cache-examples' => date('Y-m-d H:i:s'),
            'visitor.ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ], 86400);

        // Get múltiplo - interface Laravel  
        $visitorData = Cache::many(['last.visit.cache-examples', 'visitor.ip']);

        return $this->view('examples.incremental-cache', [
            'title' => 'Cache Incremental - Interface Laravel',
            'pageViews' => $pageViews,
            'apiCalls' => $apiCalls,
            'visitorData' => $visitorData
        ]);
    }

    /**
     * Exemplo 5: Cache forever (interface Laravel EXATA)
     */
    public function foreverCache(): string
    {
        // Cache forever - interface Laravel
        $systemConfig = Cache::rememberForever('system.config', function () {
            return [
                'app_name' => 'GuepardoSys Micro PHP',
                'version' => '1.0.0-dev',
                'supported_languages' => ['pt-BR', 'en-US', 'es-ES'],
                'max_upload_size' => '10MB',
                'features' => [
                    'laravel_cache_interface' => true,
                    'template_engine' => true,
                    'database_migrations' => true,
                    'cli_tools' => true
                ]
            ];
        });

        return $this->view('examples.forever-cache', [
            'title' => 'Cache Forever - Interface Laravel',
            'config' => $systemConfig
        ]);
    }

    /**
     * Limpar cache específico (interface Laravel EXATA)
     */
    public function clearCache(): string
    {
        $action = $_GET['action'] ?? 'info';
        $message = '';
        $success = false;

        switch ($action) {
            case 'users':
                // Limpa cache com tag - interface Laravel
                Cache::tags(['users'])->flush();
                $message = 'Cache de usuários limpo!';
                $success = true;
                break;

            case 'all':
                // Limpa todo cache - interface Laravel
                $success = Cache::flush();
                $message = $success ? 'Todo cache limpo!' : 'Erro ao limpar';
                break;

            case 'expired':
                // Extensão: limpa expirados
                $cleaned = Cache::cleanExpired();
                $message = "Limpas {$cleaned} entradas expiradas";
                $success = true;
                break;

            case 'multiple':
                // Remove múltiplos - interface Laravel
                $success = Cache::forgetMultiple(['test.1', 'test.2', 'test.3']);
                $message = $success ? 'Múltiplos removidos!' : 'Erro';
                break;
        }

        $stats = Cache::stats();

        return $this->view('examples.clear-cache', [
            'title' => 'Gerenciamento - Interface Laravel',
            'message' => $message,
            'success' => $success,
            'stats' => $stats
        ]);
    }
}
