<?php

namespace App\Controllers;

use GuepardoSys\Core\Cache\CacheFacade;
use App\Models\User;

/**
 * Cache Examples Controller
 * 
 * Demonstra diferentes formas de usar o cache no GuepardoSys
 */
class CacheExamplesController extends BaseController
{
    /**
     * Exemplo 1: Cache básico com helper
     */
    public function basicCache(): string
    {
        // Usando helper function - mais simples
        $expensiveData = cache_remember('expensive.calculation', function () {
            // Simula operação custosa
            sleep(1);
            return [
                'result' => rand(1, 1000),
                'calculated_at' => date('Y-m-d H:i:s'),
                'processing_time' => '1 second'
            ];
        }, 300); // Cache por 5 minutos

        return $this->view('examples.basic-cache', [
            'title' => 'Cache Básico',
            'data' => $expensiveData
        ]);
    }

    /**
     * Exemplo 2: Cache com tags (para invalidação em grupo)
     */
    public function taggedCache(): string
    {
        // Cache com tags - útil para invalidar grupos relacionados
        $userData = cache_tags(['users', 'profiles'])->remember('user.1.profile', function () {
            // Simula busca de dados do usuário
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
        }, 1800); // Cache por 30 minutos

        // Cache de estatísticas do usuário com mesmas tags
        $userStats = cache_tags(['users', 'statistics'])->remember('user.1.stats', function () {
            return [
                'total_posts' => rand(10, 100),
                'total_comments' => rand(50, 500),
                'reputation' => rand(100, 1000),
                'joined_date' => '2024-01-15'
            ];
        }, 3600); // Cache por 1 hora

        return $this->view('examples.tagged-cache', [
            'title' => 'Cache com Tags',
            'userData' => $userData,
            'userStats' => $userStats
        ]);
    }

    /**
     * Exemplo 3: Cache de consultas de banco
     */
    public function databaseCache(): string
    {
        // Cache de consulta de usuários mais eficiente
        $users = CacheFacade::remember('users.all', function () {
            // Em um cenário real, seria: User::all()
            return [
                ['id' => 1, 'name' => 'João', 'email' => 'joao@test.com'],
                ['id' => 2, 'name' => 'Maria', 'email' => 'maria@test.com'],
                ['id' => 3, 'name' => 'Pedro', 'email' => 'pedro@test.com']
            ];
        }, 600); // Cache por 10 minutos

        // Cache de contagem para dashboard
        $userCount = CacheFacade::remember('users.count', function () {
            // Simula: User::count()
            return count($users ?? []);
        }, 1800);

        return $this->view('examples.database-cache', [
            'title' => 'Cache de Banco de Dados',
            'users' => $users,
            'userCount' => $userCount
        ]);
    }

    /**
     * Exemplo 4: Cache incremental (contadores)
     */
    public function incrementalCache(): string
    {
        // Incrementa contador de visitas
        $pageViews = CacheFacade::increment('page.views.cache-examples');

        // Incrementa contador de API calls
        $apiCalls = CacheFacade::increment('api.calls.today', 1);

        // Cache de timestamp da última visita
        CacheFacade::put('last.visit.cache-examples', date('Y-m-d H:i:s'), 86400);

        return $this->view('examples.incremental-cache', [
            'title' => 'Cache Incremental',
            'pageViews' => $pageViews,
            'apiCalls' => $apiCalls,
            'lastVisit' => CacheFacade::get('last.visit.cache-examples')
        ]);
    }

    /**
     * Exemplo 5: Cache forever (dados que raramente mudam)
     */
    public function foreverCache(): string
    {
        // Cache de configurações do sistema
        $systemConfig = CacheFacade::rememberForever('system.config', function () {
            return [
                'app_name' => 'GuepardoSys Micro PHP',
                'version' => '1.0.0-dev',
                'supported_languages' => ['pt-BR', 'en-US', 'es-ES'],
                'max_upload_size' => '10MB',
                'features' => [
                    'cache_system' => true,
                    'template_engine' => true,
                    'database_migrations' => true,
                    'cli_tools' => true
                ]
            ];
        });

        return $this->view('examples.forever-cache', [
            'title' => 'Cache Forever',
            'config' => $systemConfig
        ]);
    }

    /**
     * Limpar cache específico
     */
    public function clearCache(): string
    {
        $action = $_GET['action'] ?? 'info';

        $message = '';
        $success = false;

        switch ($action) {
            case 'users':
                // Limpa cache com tag 'users'
                cache_tags(['users'])->flush();
                $message = 'Cache de usuários limpo com sucesso!';
                $success = true;
                break;

            case 'all':
                // Limpa todo o cache
                $success = cache_flush();
                $message = $success ? 'Todo cache limpo!' : 'Erro ao limpar cache';
                break;

            case 'expired':
                // Limpa apenas entradas expiradas
                $cleaned = CacheFacade::cleanExpired();
                $message = "Limpas {$cleaned} entradas expiradas";
                $success = true;
                break;
        }

        // Estatísticas do cache
        $stats = CacheFacade::stats();

        return $this->view('examples.clear-cache', [
            'title' => 'Gerenciamento de Cache',
            'message' => $message,
            'success' => $success,
            'stats' => $stats
        ]);
    }
}
