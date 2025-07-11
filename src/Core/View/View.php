<?php

namespace GuepardoSys\Core\View;

use Exception;

/**
 * Template Engine simples
 */
class View
{
    private string $viewsPath;
    private array $data = [];

    public function __construct(string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?? (defined('APP_PATH') ? APP_PATH . '/Views' : __DIR__ . '/../../app/Views');
    }

    /**
     * Renderiza uma view
     */
    public function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);

        $viewFile = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }

        // Extract data to variables
        extract($this->data);

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Adiciona dados à view
     */
    public function with(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Adiciona múltiplos dados
     */
    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Método estático para renderização rápida
     */
    public static function make(string $view, array $data = []): string
    {
        $instance = new static();
        return $instance->render($view, $data);
    }
}
