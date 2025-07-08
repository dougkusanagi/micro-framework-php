<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Route List Command
 */
class RouteListCommand extends BaseCommand
{
    public function getDescription(): string
    {
        return 'List all registered routes';
    }

    public function execute(array $args): void
    {
        $this->info("Loading routes...");

        $routesFile = BASE_PATH . '/routes/web.php';

        if (!file_exists($routesFile)) {
            $this->error("Routes file not found: {$routesFile}");
            return;
        }

        $routes = require $routesFile;

        if (empty($routes)) {
            $this->warning("No routes found");
            return;
        }

        $this->displayRoutes($routes);
    }

    private function displayRoutes(array $routes): void
    {
        $this->info("Registered Routes:");
        echo PHP_EOL;

        // Table header
        $this->printTableHeader();

        foreach ($routes as $route) {
            if (count($route) >= 3) {
                [$method, $path, $handler] = $route;
                $this->printRouteRow($method, $path, $handler);
            }
        }

        echo PHP_EOL;
        $this->info("Total routes: " . count($routes));
    }

    private function printTableHeader(): void
    {
        $header = sprintf(
            "| %-8s | %-30s | %-40s |",
            'Method',
            'Path',
            'Handler'
        );

        $separator = '+' . str_repeat('-', 10) . '+' . str_repeat('-', 32) . '+' . str_repeat('-', 42) . '+';

        echo $separator . PHP_EOL;
        echo $header . PHP_EOL;
        echo $separator . PHP_EOL;
    }

    private function printRouteRow(string $method, string $path, $handler): void
    {
        $handlerString = $this->formatHandler($handler);

        $row = sprintf(
            "| %-8s | %-30s | %-40s |",
            $method,
            $path,
            $handlerString
        );

        echo $row . PHP_EOL;
    }

    private function formatHandler($handler): string
    {
        if (is_array($handler) && count($handler) >= 2) {
            $class = is_string($handler[0]) ? $handler[0] : get_class($handler[0]);
            $method = $handler[1];

            // Shorten class name
            $classParts = explode('\\', $class);
            $shortClass = end($classParts);

            return "{$shortClass}@{$method}";
        }

        if (is_string($handler)) {
            return $handler;
        }

        return 'Closure';
    }
}
