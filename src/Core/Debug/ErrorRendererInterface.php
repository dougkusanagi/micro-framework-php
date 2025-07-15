<?php

namespace GuepardoSys\Core\Debug;

use Throwable;

/**
 * Interface for error renderers
 */
interface ErrorRendererInterface
{
    /**
     * Render a detailed error page for an exception
     *
     * @param Throwable $exception The exception to render
     * @param array $context Additional context information
     * @return string The rendered HTML output
     */
    public function render(Throwable $exception, array $context = []): string;

    /**
     * Render a detailed error page for a PHP error
     *
     * @param string $type The error type
     * @param string $message The error message
     * @param string $file The file where the error occurred
     * @param int $line The line number where the error occurred
     * @return string The rendered HTML output
     */
    public function renderError(string $type, string $message, string $file, int $line): string;
}