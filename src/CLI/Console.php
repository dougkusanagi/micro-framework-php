<?php

namespace GuepardoSys\CLI;

use GuepardoSys\CLI\Commands\ServeCommand;
use GuepardoSys\CLI\Commands\MakeControllerCommand;
use GuepardoSys\CLI\Commands\MakeModelCommand;
use GuepardoSys\CLI\Commands\RouteListCommand;
use GuepardoSys\CLI\Commands\HelpCommand;

/**
 * Console Application
 */
class Console
{
    private array $commands = [];

    public function __construct()
    {
        $this->registerCommands();
    }

    /**
     * Register available commands
     */
    private function registerCommands(): void
    {
        $this->commands = [
            'serve' => ServeCommand::class,
            'make:controller' => MakeControllerCommand::class,
            'make:model' => MakeModelCommand::class,
            'route:list' => RouteListCommand::class,
            'help' => HelpCommand::class,
        ];
    }

    /**
     * Run the console application
     */
    public function run(array $argv): void
    {
        $commandName = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        if (!isset($this->commands[$commandName])) {
            $this->showError("Command '{$commandName}' not found.");
            $this->showAvailableCommands();
            return;
        }

        $commandClass = $this->commands[$commandName];
        $command = new $commandClass();
        $command->execute($args);
    }

    /**
     * Show error message
     */
    private function showError(string $message): void
    {
        echo "\033[31mError: {$message}\033[0m" . PHP_EOL;
    }

    /**
     * Show available commands
     */
    private function showAvailableCommands(): void
    {
        echo PHP_EOL . "\033[33mAvailable commands:\033[0m" . PHP_EOL;
        foreach ($this->commands as $name => $class) {
            $command = new $class();
            echo "  \033[32m{$name}\033[0m - {$command->getDescription()}" . PHP_EOL;
        }
    }

    /**
     * Get all registered commands
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
