# Testes e Qualidade de Código - GuepardoSys Micro PHP

## Visão Geral

O framework implementa um sistema completo de testes e análise de qualidade de código usando ferramentas modernas e padrões da indústria.

## Ferramentas Implementadas

### 1. PestPHP - Framework de Testes

**Pest** é um framework de testes moderno e elegante para PHP, construído sobre o PHPUnit.

#### Comandos Disponíveis:
```bash
# Executar todos os testes
./guepardo test

# Executar com cobertura de código
./guepardo test --coverage

# Executar testes específicos
./guepardo test --filter="RouterTest"

# Executar apenas testes unitários
./guepardo test --testsuite=Unit

# Executar apenas testes de funcionalidade
./guepardo test --testsuite=Feature

# Executar em paralelo
./guepardo test --parallel
```

## Test Structure

```
tests/
├── Feature/           # Integration tests
├── Unit/             # Unit tests  
├── Pest.php          # Pest configuration
├── TestCase.php      # Base test case
└── helpers.php       # Test helpers
```

## Running Tests

### Basic Commands

```bash
# Run all tests
./guepardo test

# Run with coverage
./guepardo test --coverage

# Run specific test suite
./guepardo test --testsuite=Unit
./guepardo test --testsuite=Feature

# Run with filter
./guepardo test --filter=UserTest

# Run in parallel
./guepardo test --parallel
```

### Using Makefile

```bash
# Run all tests
make test

# Run with coverage
make test-coverage

# Run only unit tests
make test-unit

# Run only feature tests
make test-feature
```

## Writing Tests

### Unit Tests

```php
<?php

test('user can be created', function () {
    $user = new User();
    expect($user)->toBeInstanceOf(User::class);
});

test('user email validation', function () {
    expect('test@example.com')->toBeValidEmail();
    expect('invalid-email')->not()->toBeValidEmail();
});
```

### Feature Tests

```php
<?php

test('home page loads successfully', function () {
    $request = $this->createRequest('GET', '/');
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri())->toBe('/');
});

test('authenticated user can access dashboard', function () {
    $user = create_test_user();
    $this->actingAs($user);
    
    $request = $this->createRequest('GET', '/dashboard');
    expect($_SESSION['user_id'])->toBe($user['id']);
});
```

## Available Helpers

### Test Case Helpers

- `createRequest($method, $uri, $data)` - Create HTTP request
- `actingAs($user)` - Simulate authenticated user  
- `clearSession()` - Clear session data

### Global Helpers

- `create_test_user($overrides)` - Create test user array
- `temp_view($name, $content)` - Create temporary view
- `cleanup_temp_view($path)` - Remove temporary view

### Custom Expectations

- `toBeValidEmail()` - Validate email format
- `toBeValidUrl()` - Validate URL format

## Configuration

### PHPUnit XML

Test configuration is defined in `phpunit.xml`:

```xml
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Pest Configuration

Custom expectations and helpers are configured in `tests/Pest.php`.

## Best Practices

1. **Test Organization**: Keep unit tests in `tests/Unit/` and integration tests in `tests/Feature/`

2. **Test Naming**: Use descriptive test names that explain what is being tested

3. **Arrange-Act-Assert**: Structure tests clearly with setup, execution, and assertion phases

4. **Use Helpers**: Leverage provided helpers for common testing tasks

5. **Mock External Dependencies**: Avoid real database calls in unit tests when possible

6. **Coverage**: Aim for high test coverage, but focus on quality over quantity

## CI/CD Integration

Tests run automatically in GitHub Actions on push and pull requests. See `.github/workflows/quality.yml` for configuration.

## Troubleshooting

### Common Issues

1. **Autoloading**: Ensure `composer install` has been run
2. **Permissions**: Check `storage/` directory permissions
3. **Environment**: Verify test environment configuration

### Debug Mode

Run tests with verbose output:

```bash
vendor/bin/pest --verbose
```
