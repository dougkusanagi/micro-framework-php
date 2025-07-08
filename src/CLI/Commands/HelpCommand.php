<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Help Command
 */
class HelpCommand extends BaseCommand
{
    public function getDescription(): string
    {
        return 'Show available commands';
    }

    public function execute(array $args): void
    {
        $this->showHeader();
        $this->showCommands();
        $this->showFooter();
    }

    private function showHeader(): void
    {
        echo PHP_EOL;
        echo "\033[36m";
        echo "  ____                              _       ____            " . PHP_EOL;
        echo " / ___|_   _  ___ _ __   __ _ _ __ __| | ___ / ___| _   _ ___ " . PHP_EOL;
        echo "| |  _| | | |/ _ \ '_ \ / _\` | '__/ _\` |/ _ \\\___ \| | | / __|" . PHP_EOL;
        echo "| |_| | |_| |  __/ |_) | (_| | | | (_| | (_) |___) | |_| \__ \\" . PHP_EOL;
        echo " \____|\__,_|\___| .__/ \__,_|_|  \__,_|\___/|____/ \__, |___/" . PHP_EOL;
        echo "                 |_|                               |___/     " . PHP_EOL;
        echo "\033[0m";
        echo "\033[33mGuepardoSys Micro PHP - CLI Tool\033[0m" . PHP_EOL;
        echo "\033[37mVersion 1.0.0\033[0m" . PHP_EOL;
        echo PHP_EOL;
    }

    private function showCommands(): void
    {
        echo "\033[33mAvailable Commands:\033[0m" . PHP_EOL;
        echo PHP_EOL;

        // Get commands from Console class
        $console = new \GuepardoSys\CLI\Console();
        $commands = $console->getCommands();

        foreach ($commands as $name => $class) {
            if ($name === 'help') continue; // Skip help command to avoid recursion

            $command = new $class();
            echo "  \033[32m{$name}\033[0m" . PHP_EOL;
            echo "    {$command->getDescription()}" . PHP_EOL;
            echo PHP_EOL;
        }
    }

    private function showFooter(): void
    {
        echo "\033[37mFor more information, visit: https://github.com/guepardosys/micro-php\033[0m" . PHP_EOL;
        echo PHP_EOL;
    }
}
