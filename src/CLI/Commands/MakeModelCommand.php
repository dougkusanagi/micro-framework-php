<?php

namespace GuepardoSys\CLI\Commands;

/**
 * Make Model Command
 */
class MakeModelCommand extends BaseCommand
{
    public function getDescription(): string
    {
        return 'Generate a new model';
    }

    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error("Model name is required");
            $this->info("Usage: php guepardo make:model ModelName");
            return;
        }

        $modelName = $args[0];

        // Ensure proper naming (singular, PascalCase)
        $modelName = ucfirst($modelName);

        $this->createModel($modelName);
    }

    private function createModel(string $modelName): void
    {
        $modelPath = APP_PATH . '/Models/' . $modelName . '.php';

        if (file_exists($modelPath)) {
            $this->error("Model already exists: {$modelName}");
            return;
        }

        // Create Models directory if it doesn't exist
        $this->createDirectory(APP_PATH . '/Models');

        try {
            $stub = $this->getStub('model');

            // Generate table name (plural, snake_case)
            $tableName = $this->generateTableName($modelName);

            $content = $this->replaceStubVariables($stub, [
                'ModelName' => $modelName,
                'ModelNamespace' => 'App\\Models',
                'TableName' => $tableName
            ]);

            file_put_contents($modelPath, $content);

            $this->success("Model created successfully: {$modelName}");
            $this->info("Path: {$modelPath}");
            $this->info("Table name: {$tableName}");
        } catch (\Exception $e) {
            $this->error("Failed to create model: " . $e->getMessage());
        }
    }

    private function generateTableName(string $modelName): string
    {
        // Convert PascalCase to snake_case
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));

        // Simple pluralization
        if (substr($tableName, -1) === 'y') {
            $tableName = substr($tableName, 0, -1) . 'ies';
        } elseif (substr($tableName, -1) === 's') {
            $tableName .= 'es';
        } else {
            $tableName .= 's';
        }

        return $tableName;
    }
}
