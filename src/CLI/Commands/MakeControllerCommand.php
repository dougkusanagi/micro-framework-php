<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Make Controller Command
 */
class MakeControllerCommand extends BaseCommand
{
    public function getDescription(): string
    {
        return 'Generate a new controller';
    }

    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error("Controller name is required");
            $this->info("Usage: php guepardo make:controller ControllerName");
            return;
        }

        $controllerName = $args[0];

        // Ensure Controller suffix
        if (!str_ends_with($controllerName, 'Controller')) {
            $controllerName .= 'Controller';
        }

        $this->createController($controllerName);
    }

    private function createController(string $controllerName): void
    {
        $controllerPath = APP_PATH . '/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerPath)) {
            $this->error("Controller already exists: {$controllerName}");
            return;
        }

        // Create Controllers directory if it doesn't exist
        $this->createDirectory(APP_PATH . '/Controllers');

        try {
            $stub = $this->getStub('controller');

            // Generate view path from controller name
            $viewPath = $this->generateViewPath($controllerName);

            $content = $this->replaceStubVariables($stub, [
                'ControllerName' => $controllerName,
                'ControllerNamespace' => 'App\\Controllers',
                'viewPath' => $viewPath
            ]);

            file_put_contents($controllerPath, $content);

            $this->success("Controller created successfully: {$controllerName}");
            $this->info("Path: {$controllerPath}");
            $this->info("Suggested view path: {$viewPath}");
        } catch (\Exception $e) {
            $this->error("Failed to create controller: " . $e->getMessage());
        }
    }

    private function generateViewPath(string $controllerName): string
    {
        // Remove Controller suffix and convert to snake_case
        $name = str_replace('Controller', '', $controllerName);
        $viewPath = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));

        // Pluralize for resources
        if (substr($viewPath, -1) === 'y') {
            $viewPath = substr($viewPath, 0, -1) . 'ies';
        } elseif (substr($viewPath, -1) === 's') {
            $viewPath .= 'es';
        } else {
            $viewPath .= 's';
        }

        return $viewPath;
    }
}
