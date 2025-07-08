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

        $commands = [
            'serve' => [
                'description' => 'Start the development server',
                'usage' => 'serve [host] [port]',
                'example' => 'serve localhost 8000'
            ],
            'make:controller' => [
                'description' => 'Generate a new controller',
                'usage' => 'make:controller <name>',
                'example' => 'make:controller UserController'
            ],
            'make:model' => [
                'description' => 'Generate a new model',
                'usage' => 'make:model <name>',
                'example' => 'make:model Product'
            ],
            'route:list' => [
                'description' => 'List all registered routes',
                'usage' => 'route:list',
                'example' => 'route:list'
            ],
            'help' => [
                'description' => 'Show this help message',
                'usage' => 'help',
                'example' => 'help'
            ]
        ];

        foreach ($commands as $name => $info) {
            echo "\033[32m  {$name}\033[0m" . PHP_EOL;
            echo "    {$info['description']}" . PHP_EOL;
            echo "    \033[90mUsage: ./guepardo {$info['usage']}\033[0m" . PHP_EOL;
            echo "    \033[90mExample: ./guepardo {$info['example']}\033[0m" . PHP_EOL;
            echo PHP_EOL;
        }
    }

    private function showFooter(): void
    {
        echo "\033[37mFor more information, visit: https://github.com/guepardosys/micro-php\033[0m" . PHP_EOL;
        echo PHP_EOL;
    }
}
