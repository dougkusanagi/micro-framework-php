<?php

namespace GuepardoSys\Core\Security;

/**
 * Input Validator and Sanitizer
 * Provides validation and sanitization of user input
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $customMessages = [];

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    /**
     * Validate data against rules
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $rules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($rules as $rule) {
                $this->validateRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Check if validation passes
     */
    public function passes(): bool
    {
        return $this->validate();
    }

    /**
     * Check if validation fails
     */
    public function fails(): bool
    {
        return !$this->validate();
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function first(string $field): ?string
    {
        $errors = $this->errors();
        return isset($errors[$field]) && !empty($errors[$field]) ? $errors[$field][0] : null;
    }

    /**
     * Get sanitized data
     */
    public function sanitized(): array
    {
        $sanitized = [];

        foreach ($this->data as $key => $value) {
            $sanitized[$key] = $this->sanitize($value);
        }

        return $sanitized;
    }

    /**
     * Validate a single rule
     */
    private function validateRule(string $field, mixed $value, string $rule): void
    {
        // Parse rule and parameters
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $parameters = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'required');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'email');
                }
                break;

            case 'min':
                $min = (int)($parameters[0] ?? 0);
                if (!empty($value) && strlen($value) < $min) {
                    $this->addError($field, 'min', ['min' => $min]);
                }
                break;

            case 'max':
                $max = (int)($parameters[0] ?? 0);
                if (!empty($value) && strlen($value) > $max) {
                    $this->addError($field, 'max', ['max' => $max]);
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'numeric');
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'integer');
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, 'alpha');
                }
                break;

            case 'alphanum':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, 'alphanum');
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'url');
                }
                break;

            case 'regex':
                $pattern = $parameters[0] ?? '';
                if (!empty($value) && !empty($pattern) && !preg_match($pattern, $value)) {
                    $this->addError($field, 'regex');
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $parameters)) {
                    $this->addError($field, 'in', ['values' => implode(', ', $parameters)]);
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!empty($value) && $value !== ($this->data[$confirmField] ?? '')) {
                    $this->addError($field, 'confirmed');
                }
                break;
        }
    }

    /**
     * Add validation error
     */
    private function addError(string $field, string $rule, array $parameters = []): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $message = $this->getErrorMessage($field, $rule, $parameters);
        $this->errors[$field][] = $message;
    }

    /**
     * Get error message for rule
     */
    private function getErrorMessage(string $field, string $rule, array $parameters = []): string
    {
        $messages = [
            'required' => "The {field} field is required.",
            'email' => "The {field} must be a valid email address.",
            'min' => "The {field} must be at least {min} characters.",
            'max' => "The {field} may not be greater than {max} characters.",
            'numeric' => "The {field} must be a number.",
            'integer' => "The {field} must be an integer.",
            'alpha' => "The {field} may only contain letters.",
            'alphanum' => "The {field} may only contain letters and numbers.",
            'url' => "The {field} must be a valid URL.",
            'regex' => "The {field} format is invalid.",
            'in' => "The selected {field} is invalid. Valid options: {values}.",
            'confirmed' => "The {field} confirmation does not match."
        ];

        $message = $messages[$rule] ?? "The {field} is invalid.";

        // Replace placeholders
        $message = str_replace('{field}', ucfirst(str_replace('_', ' ', $field)), $message);

        foreach ($parameters as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    /**
     * Sanitize input value
     */
    private function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            // Remove null bytes
            $value = str_replace(chr(0), '', $value);

            // Trim whitespace
            $value = trim($value);

            // Basic HTML sanitization
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }

    /**
     * Static validation helper
     */
    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    /**
     * Quick validation check
     */
    public static function validate_quick(array $data, array $rules): bool
    {
        $validator = new self($data, $rules);
        return $validator->validate();
    }

    /**
     * Sanitize array of data
     */
    public static function sanitize_array(array $data): array
    {
        $validator = new self($data, []);
        return $validator->sanitized();
    }
}
