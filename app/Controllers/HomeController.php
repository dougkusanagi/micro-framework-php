<?php

namespace App\Controllers;

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
     * Display the about page
     */
    public function about(): string
    {
        $data = [
            'appName' => 'GuepardoSys Micro PHP',
            'currentRoute' => 'about',
            'phpVersion' => PHP_VERSION,
            'frameworkVersion' => '1.0.0-dev',
            'environment' => $_ENV['APP_ENV'] ?? 'development',
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
        ];

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
}
