<?php

namespace GuepardoSys\CLI;

use GuepardoSys\CLI\Commands\ServeCommand;
use GuepardoSys\CLI\Commands\MakeControllerCommand;
use GuepardoSys\CLI\Commands\MakeModelCommand;
use GuepardoSys\CLI\Commands\RouteListCommand;
use GuepardoSys\CLI\Commands\HelpCommand;
use GuepardoSys\CLI\Commands\MigrateUpCommand;
use GuepardoSys\CLI\Commands\MigrateDownCommand;
use GuepardoSys\CLI\Commands\MigrateSeedCommand;
use GuepardoSys\CLI\Commands\MigrateStatusCommand;
use GuepardoSys\CLI\Commands\MakeMigrationCommand;
use GuepardoSys\CLI\Commands\MigrateCommand;
use GuepardoSys\CLI\Commands\MigrateRollbackCommand;
use GuepardoSys\CLI\Commands\MigrateRefreshCommand;
use GuepardoSys\CLI\Commands\DbSeedCommand;
use GuepardoSys\CLI\Commands\AssetCommand;
use GuepardoSys\CLI\Commands\CacheClearCommand;
use GuepardoSys\CLI\Commands\OptimizeCommand;
use GuepardoSys\CLI\Commands\TestCommand;
use GuepardoSys\CLI\Commands\StanCommand;
use GuepardoSys\CLI\Commands\CsCommand;
use GuepardoSys\CLI\Commands\QualityCommand;
use GuepardoSys\CLI\Commands\BuildCommand;
use GuepardoSys\CLI\Commands\DeployCommand;
use GuepardoSys\CLI\Commands\HealthCommand;

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
            'make:migration' => MakeMigrationCommand::class,
            'route:list' => RouteListCommand::class,

            // Main migration commands (Artisan style)
            'migrate' => MigrateCommand::class,
            'migrate:rollback' => MigrateRollbackCommand::class,
            'migrate:refresh' => MigrateRefreshCommand::class,
            'migrate:status' => MigrateStatusCommand::class,
            'db:seed' => DbSeedCommand::class,

            // Legacy migration commands (backward compatibility)
            'migrate:up' => MigrateUpCommand::class,
            'migrate:down' => MigrateDownCommand::class,
            'migrate:seed' => MigrateSeedCommand::class,

            // Asset commands (temporariamente removidos até implementação correta)
            // 'asset:build' => AssetCommand::class,
            // 'asset:dev' => AssetCommand::class,
            // 'asset:clean' => AssetCommand::class,

            // Cache commands
            'cache:clear' => CacheClearCommand::class,

            // Optimization commands
            'optimize' => OptimizeCommand::class,

            // Quality and testing commands
            'test' => TestCommand::class,
            'stan' => StanCommand::class,
            'cs' => CsCommand::class,
            'quality' => QualityCommand::class,

            // Build and deployment commands
            'build' => BuildCommand::class,
            'deploy' => DeployCommand::class,

            // Health check command
            'health' => HealthCommand::class,
            'deploy' => DeployCommand::class,

            // Health check command
            'health' => HealthCommand::class,

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

        $commandConfig = $this->commands[$commandName];

        // Check if command has specific method
        if (strpos($commandConfig, '@') !== false) {
            [$commandClass, $method] = explode('@', $commandConfig);
            $command = new $commandClass($this);
            $command->$method($args);
        } else {
            $command = new $commandConfig();
            $command->execute($args);
        }
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

    /**
     * Output methods for colored console output
     */
    public function info(string $message): void
    {
        echo "\033[34m{$message}\033[0m\n";
    }

    public function success(string $message): void
    {
        echo "\033[32m{$message}\033[0m\n";
    }

    public function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m\n";
    }

    public function warning(string $message): void
    {
        echo "\033[33m{$message}\033[0m\n";
    }

    public function line(string $message): void
    {
        echo "{$message}\n";
    }
}
