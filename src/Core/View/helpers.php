<?php

use GuepardoSys\Core\View\View;

/**
 * Funções helper para o sistema de views
 */

if (!function_exists('view')) {
    /**
     * Renderiza uma view usando o template engine
     */
    function view(string $view, array $data = []): string
    {
        static $viewEngine = null;

        if ($viewEngine === null) {
            $viewEngine = new View();
        }

        return $viewEngine->render($view, $data);
    }
}

if (!function_exists('template')) {
    /**
     * Alias para a função view
     */
    function template(string $view, array $data = []): string
    {
        return view($view, $data);
    }
}

if (!function_exists('clearViewCache')) {
    /**
     * Limpa o cache de views
     */
    function clearViewCache(): int
    {
        $viewEngine = new View();
        return $viewEngine->clearCache();
    }
}
