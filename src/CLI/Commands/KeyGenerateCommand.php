<?php

namespace GuepardoSys\CLI\Commands;

class KeyGenerateCommand extends BaseCommand
{
    public function execute(array $args): void
    {
        $envPath = BASE_PATH . '/.env';
        $key = 'base64:' . base64_encode(random_bytes(32));
        $envContent = '';
        $updated = false;

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
                $updated = true;
            } else {
                $envContent .= (str_ends_with($envContent, "\n") ? '' : "\n") . 'APP_KEY=' . $key . "\n";
            }
        } else {
            $envContent = "APP_KEY={$key}\n";
        }

        file_put_contents($envPath, $envContent);

        if ($updated) {
            $this->success('APP_KEY updated in .env file.');
        } else {
            $this->success('APP_KEY set in .env file.');
        }
        $this->info('Key: ' . $key);
    }

    public function getDescription(): string
    {
        return 'Set the APP_KEY value in your .env file';
    }
}
