# Design Document - Advanced Error Debug System

## Overview

O sistema de debug avançado será implementado como uma extensão do ErrorHandler existente, criando uma interface visual rica similar ao Laravel Ignition. O sistema manterá a filosofia de leveza do framework, utilizando apenas recursos nativos do PHP e CSS/JavaScript inline, sem dependências externas.

## Architecture

### Core Components

1. **AdvancedErrorRenderer** - Responsável por renderizar a interface de debug
2. **SourceCodeExtractor** - Extrai e formata código fonte ao redor dos erros
3. **ContextCollector** - Coleta informações de contexto da requisição
4. **StackTraceFormatter** - Formata e organiza o stack trace
5. **ErrorTemplateEngine** - Gera o HTML da página de erro

### Integration Points

O sistema se integrará com o ErrorHandler existente através de:
- Extensão dos métodos `displayException()` e `displayError()`
- Utilização da variável `APP_DEBUG` já implementada
- Reutilização do sistema de logging existente

## Components and Interfaces

### 1. AdvancedErrorRenderer

```php
interface ErrorRendererInterface
{
    public function render(Throwable $exception, array $context = []): string;
    public function renderError(string $type, string $message, string $file, int $line): string;
}

class AdvancedErrorRenderer implements ErrorRendererInterface
{
    private SourceCodeExtractor $sourceExtractor;
    private ContextCollector $contextCollector;
    private StackTraceFormatter $stackFormatter;
    
    public function render(Throwable $exception, array $context = []): string;
    public function renderError(string $type, string $message, string $file, int $line): string;
    private function generateHTML(array $data): string;
}
```

### 2. SourceCodeExtractor

```php
class SourceCodeExtractor
{
    public function extract(string $file, int $line, int $contextLines = 10): array;
    public function highlightSyntax(string $code, string $language = 'php'): string;
    private function getFileLines(string $file, int $start, int $end): array;
}
```

### 3. ContextCollector

```php
class ContextCollector
{
    public function collect(): array;
    private function getRequestData(): array;
    private function getServerData(): array;
    private function getEnvironmentData(): array;
    private function sanitizeSensitiveData(array $data): array;
}
```

### 4. StackTraceFormatter

```php
class StackTraceFormatter
{
    public function format(array $trace): array;
    public function formatFrame(array $frame): array;
    private function isVendorFrame(array $frame): bool;
    private function isApplicationFrame(array $frame): bool;
}
```

## Data Models

### Error Data Structure

```php
[
    'error' => [
        'type' => string,
        'message' => string,
        'file' => string,
        'line' => int,
        'class' => string|null,
        'code' => int
    ],
    'source' => [
        'file' => string,
        'line' => int,
        'code' => array, // Array of line numbers => code
        'highlighted_line' => int
    ],
    'stack_trace' => [
        [
            'file' => string,
            'line' => int,
            'function' => string,
            'class' => string|null,
            'type' => string,
            'args' => array,
            'source' => array|null,
            'is_vendor' => bool,
            'is_application' => bool
        ]
    ],
    'context' => [
        'request' => [
            'method' => string,
            'url' => string,
            'headers' => array,
            'get' => array,
            'post' => array,
            'files' => array
        ],
        'session' => array,
        'server' => array,
        'environment' => array
    ],
    'suggestions' => array // Possíveis soluções baseadas no tipo de erro
]
```

## Error Handling

### Error Type Detection

O sistema detectará diferentes tipos de erro e fornecerá informações específicas:

1. **Syntax Errors** - Destacar linha problemática com sugestões
2. **Database Errors** - Mostrar query SQL e parâmetros
3. **HTTP Errors (404)** - Listar rotas disponíveis
4. **Validation Errors** - Destacar campos problemáticos
5. **Custom Exceptions** - Informações específicas da exceção

### Security Considerations

- Mascarar dados sensíveis (senhas, tokens, chaves API)
- Sanitizar output para prevenir XSS
- Validar caminhos de arquivo para prevenir path traversal
- Limitar informações expostas baseado no ambiente

## Testing Strategy

### Unit Tests

1. **SourceCodeExtractor**
   - Teste de extração de código com diferentes tamanhos de contexto
   - Teste de syntax highlighting
   - Teste com arquivos inexistentes ou ilegíveis

2. **ContextCollector**
   - Teste de coleta de dados de requisição
   - Teste de sanitização de dados sensíveis
   - Teste com diferentes tipos de requisição

3. **StackTraceFormatter**
   - Teste de formatação de stack trace
   - Teste de identificação de frames vendor vs application
   - Teste com diferentes tipos de exceção

4. **AdvancedErrorRenderer**
   - Teste de renderização completa
   - Teste de diferentes tipos de erro
   - Teste de output HTML válido

### Integration Tests

1. **ErrorHandler Integration**
   - Teste de integração com ErrorHandler existente
   - Teste de alternância entre modo debug e produção
   - Teste de diferentes tipos de exceção

2. **Template Rendering**
   - Teste de renderização de template completo
   - Teste de responsividade
   - Teste de funcionalidades JavaScript

### Visual Tests

1. **Interface Testing**
   - Teste de layout em diferentes resoluções
   - Teste de navegação entre seções
   - Teste de funcionalidades de cópia

## Implementation Details

### Template Structure

A página de erro será estruturada em seções colapsáveis:

1. **Header** - Tipo de erro, mensagem principal
2. **Source Code** - Código fonte com destaque da linha do erro
3. **Stack Trace** - Trace navegável com código fonte de cada frame
4. **Request Context** - Informações da requisição HTTP
5. **Environment** - Variáveis de ambiente e configuração
6. **Suggestions** - Sugestões baseadas no tipo de erro

### Styling Approach

- CSS inline para evitar dependências externas
- Design responsivo usando CSS Grid e Flexbox
- Tema escuro/claro baseado em preferência do sistema
- Syntax highlighting usando CSS classes
- Animações CSS para transições suaves

### JavaScript Functionality

- Navegação entre seções do stack trace
- Funcionalidade de cópia (clipboard API)
- Colapsar/expandir seções
- Busca no código fonte
- Tudo inline, sem dependências externas

### Performance Considerations

- Lazy loading de código fonte para frames não visíveis
- Limitação de contexto de código para arquivos grandes
- Cache de syntax highlighting para melhor performance
- Compressão de output HTML quando possível

### Error Type Specific Features

#### Database Errors
- Exibição da query SQL formatada
- Parâmetros da query (sanitizados)
- Informações de conexão (sem credenciais)

#### 404 Errors
- Lista de rotas disponíveis
- Sugestões de rotas similares
- Informações sobre roteamento

#### Validation Errors
- Destaque de campos problemáticos
- Regras de validação aplicadas
- Valores fornecidos vs esperados

## File Structure

```
src/Core/Debug/
├── AdvancedErrorRenderer.php
├── SourceCodeExtractor.php
├── ContextCollector.php
├── StackTraceFormatter.php
├── ErrorSuggestionEngine.php
└── Templates/
    └── error-page.html.php
```

## Configuration

O sistema utilizará as seguintes configurações de ambiente:

- `APP_DEBUG` - Ativa/desativa o modo debug
- `DEBUG_SHOW_SOURCE` - Controla exibição de código fonte (padrão: true)
- `DEBUG_CONTEXT_LINES` - Número de linhas de contexto (padrão: 10)
- `DEBUG_MAX_STRING_LENGTH` - Tamanho máximo de strings no contexto (padrão: 1000)
- `DEBUG_HIDE_VENDOR` - Oculta frames de vendor no stack trace (padrão: true)