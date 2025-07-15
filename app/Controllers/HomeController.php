<?php

namespace App\Controllers;

use GuepardoSys\Core\Cache\CacheFacade;

/**
 * Home Controller
 * 
 * Handles requests for the home page and basic routes
 */
class HomeController extends BaseController
{
    /**
     * Display the home page
     */
    public function index(): string
    {
        $data = [
            'appName' => 'GuepardoSys Micro PHP',
            'currentRoute' => 'home',
            'message' => 'Tudo funcionando',
            'showFeatures' => true,
            'features' => [
                '✅ Arquitetura MVC',
                '✅ Roteamento Simples',
                '✅ Template Engine (Blade-like)',
                '✅ Container DI',
                '✅ Cache de Views',
                '✅ Escape Automático XSS'
            ]
        ];

        return $this->view('pages.home', $data);
    }

    /**
     * Display the about page com cache usando facade
     */
    public function about(): string
    {
        // Exemplo usando Cache Facade diretamente
        $systemInfo = CacheFacade::remember('system.info', function () {
            return [
                'phpVersion' => PHP_VERSION,
                'frameworkVersion' => '1.0.0-dev',
                'environment' => $_ENV['APP_ENV'] ?? 'development',
                'loadTime' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
            ];
        }, 1800); // Cache por 30 minutos

        $data = array_merge([
            'appName' => 'GuepardoSys Micro PHP',
            'currentRoute' => 'about',
            'specs' => [
                [
                    'title' => 'Performance',
                    'description' => 'TTFB < 50ms em produção'
                ],
                [
                    'title' => 'Arquivos',
                    'description' => 'Menos de 200 arquivos no core'
                ],
                [
                    'title' => 'Compatibilidade',
                    'description' => 'Funciona em hospedagem compartilhada'
                ],
                [
                    'title' => 'Segurança',
                    'description' => 'Escape automático XSS, prepared statements'
                ]
            ]
        ], $systemInfo);

        return $this->view('pages.about', $data);
    }

    /**
     * Display template test page
     */
    public function teste(): string
    {
        $data = [
            'appName' => 'GuepardoSys Micro PHP',
            'currentRoute' => 'teste',
            'nome' => 'Sistema de Templates',
            'versao' => '1.0.0',
            'mostrarLista' => true,
            'itens' => [
                'Template Engine funcionando',
                'Cache de views implementado',
                'Escape automático XSS',
                'Diretivas @extends, @section, @yield',
                'Condicionais @if, @else, @endif',
                'Loops @foreach, @endforeach'
            ]
        ];

        return $this->view('pages.teste', $data);
    }

    /**
     * Display the frontend demo page
     */
    public function frontendDemo(): string
    {
        $data = [
            'appName' => 'GuepardoSys Micro PHP',
            'currentRoute' => 'frontend-demo'
        ];

        return $this->view('pages.frontend-demo', $data);
    }
}
