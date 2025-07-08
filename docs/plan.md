# Plano de Implementação - GuepardoSys Micro PHP

## Visão Geral

Este plano divide a implementação do GuepardoSys Micro PHP em etapas incrementais, começando com um MVP funcional e evoluindo para um framework completo conforme especificado no PRD e Blueprint.

---

## Etapa 1: MVP - Core Básico Funcional

**Objetivo**: Criar um sistema MVC mínimo que funcione com roteamento básico e exibição de views.

### 1.1 Estrutura de Diretórios Base
- [x] Criar estrutura de diretórios conforme blueprint
- [x] Configurar `composer.json` com PSR-4 autoload
- [x] Criar `.gitignore` apropriado

### 1.2 Bootstrap e Entrypoint
- [x] Implementar `public/index.php` - ponto de entrada
- [x] Criar `bootstrap/app.php` - inicialização da aplicação
- [x] Implementar classe `App` principal
- [x] Configurar carregamento básico de variáveis de ambiente

### 1.3 Roteamento Básico
- [x] Implementar classe `Router` simples
- [x] Criar sistema de matching de rotas com regex
- [x] Implementar despacho para controllers
- [x] Criar `routes/web.php` com definições de rotas

### 1.4 Controllers Base
- [x] Criar `BaseController` com métodos auxiliares
- [x] Implementar `HomeController` como exemplo
- [x] Sistema básico de instanciação e chamada de métodos

### 1.5 Views Básicas (sem template engine)
- [x] Sistema simples de include de views PHP puras
- [x] Método `view()` para carregamento de views
- [x] Estrutura básica em `app/Views/`

### 1.6 Configuração Servidor Web
- [x] Criar `.htaccess` para Apache
- [x] Documentar configuração Nginx

**Entregável**: Sistema que responde a rotas básicas e exibe views simples. ✅ **CONCLUÍDO**

---

## Etapa 2: Sistema de Templates Customizado

**Objetivo**: Implementar motor de templates inspirado no Blade para melhorar a experiência de desenvolvimento.

### 2.1 Motor de Template Base
- [x] Implementar classe `View` com sistema de compilação
- [x] Sistema de cache em `storage/cache/`
- [x] Compilação de `{{ $variavel }}` para PHP
- [x] Escape automático para segurança (XSS)

### 2.2 Diretivas de Template
- [x] Implementar `@extends` e `@yield`
- [x] Implementar `@section` e `@endsection`
- [x] Implementar `@include`
- [x] Implementar `@if`, `@else`, `@endif`
- [x] Implementar `@foreach`, `@endforeach`

### 2.3 Sistema de Layouts
- [x] Criar layout base em `app/Views/layouts/`
- [x] Sistema de herança de templates
- [x] Gestão de seções e yields

**Entregável**: Sistema de templates funcional com sintaxe amigável. ✅ **CONCLUÍDO**

---

## Etapa 3: Banco de Dados e Models

**Objetivo**: Adicionar capacidade de interação com banco de dados através de Models.

### 3.1 Configuração de Banco
- [x] Implementar carregamento de configurações de BD
- [x] Criar classe de conexão PDO
- [x] Sistema de configuração em `config/database.php`
- [x] Suporte a múltiplos SGBDs (MySQL, PostgreSQL)

### 3.2 Base Model
- [x] Implementar `BaseModel` com PDO
- [x] Métodos CRUD básicos (`find`, `all`, `create`, `update`, `delete`)
- [x] Sistema de tabelas automático por convenção
- [x] Prepared statements para segurança

### 3.3 Model de Exemplo
- [x] Criar `User` model como exemplo
- [x] Implementar relacionamentos simples
- [x] Validação básica de dados

**Entregável**: Sistema de Models funcional com operações CRUD. ✅ **CONCLUÍDO**

---

## Etapa 4: CLI Tool (guepardo)

**Objetivo**: Criar ferramenta de linha de comando para auxiliar desenvolvimento.

### 4.1 Estrutura da CLI
- [x] Criar script executável `guepardo`
- [x] Sistema de roteamento de comandos
- [x] Bootstrap mínimo para CLI

### 4.2 Comandos de Desenvolvimento
- [x] `serve` - servidor de desenvolvimento
- [x] `make:controller` - geração de controllers
- [x] `make:model` - geração de models
- [x] `route:list` - listagem de rotas

### 4.3 Templates/Stubs
- [x] Criar stubs para controllers
- [x] Criar stubs para models
- [x] Sistema de substituição de variáveis nos stubs

**Entregável**: CLI funcional com comandos básicos de desenvolvimento. ✅ **CONCLUÍDO**

---

## Etapa 5: Sistema de Migrações

**Objetivo**: Adicionar controle de versão para banco de dados.

### 5.1 Estrutura de Migrações
- [x] Sistema de arquivos SQL em `database/migrations/`
- [x] Tabela de controle de migrações
- [x] Naming convention para arquivos

### 5.2 Comandos de Migração
- [x] `migrate:up` - execução de migrações
- [x] `migrate:down` - reversão de migrações
- [x] `migrate:seed` - população de dados
- [x] `migrate` - comando principal (similar ao Artisan)
- [x] `migrate:rollback` - rollback de migrações
- [x] `migrate:refresh` - refresh completo
- [x] `migrate:status` - status das migrações
- [x] `make:migration` - criação de novas migrações

### 5.3 Sistema de Seeds
- [x] Arquivos SQL para dados iniciais
- [x] Execução via CLI
- [x] Gestão de dependências entre seeds
- [x] Comando `db:seed` separado

### 5.4 Recursos Avançados
- [x] Verificação automática de existência do banco
- [x] Criação automática do banco com confirmação
- [x] Flags `--force` e `--seed` (compatível com Artisan)
- [x] Templates inteligentes para diferentes tipos de migração

**Entregável**: Sistema completo de migrações e seeds. ✅ **CONCLUÍDO**

---

## Etapa 6: Autenticação Básica

**Objetivo**: Implementar sistema de autenticação simples.

### 6.1 Gestão de Usuários
- [ ] Migração para tabela de usuários
- [ ] Model `User` com autenticação
- [ ] Hash de senhas seguro

### 6.2 Sistema de Sessões
- [ ] Gestão de sessões PHP
- [ ] Login e logout
- [ ] Middleware de autenticação

### 6.3 Controllers de Auth
- [ ] `AuthController` para login/registro
- [ ] Views de login e registro
- [ ] Validação de formulários

**Entregável**: Sistema de autenticação funcional.

---

## Etapa 7: Frontend Workflow

**Objetivo**: Integrar ferramentas modernas de frontend.

### 7.1 Configuração Bun
- [ ] `package.json` com scripts
- [ ] Configuração Tailwind CSS
- [ ] Sistema de build para produção

### 7.2 Assets Pipeline
- [ ] Compilação de CSS
- [ ] Otimização para produção
- [ ] Versionamento de assets

### 7.3 Integração CDN
- [ ] Alpine.js via CDN
- [ ] Lucide Icons via CDN
- [ ] Google Fonts via CDN
- [ ] Glide.js para sliders

**Entregável**: Pipeline de frontend completo e otimizado.

---

## Etapa 8: Cache e Performance

**Objetivo**: Implementar sistema de cache para otimizar performance.

### 8.1 Cache de Views
- [ ] Otimizar sistema de cache de templates
- [ ] Invalidação inteligente de cache
- [ ] Compressão de cache

### 8.2 Cache de Dados
- [ ] Sistema de cache de consultas
- [ ] Cache baseado em arquivos
- [ ] TTL configurável

### 8.3 Otimizações
- [ ] Autoload otimizado
- [ ] Minimização de includes
- [ ] Lazy loading onde possível

**Entregável**: Sistema de cache robusto com performance otimizada.

---

## Etapa 9: Segurança e Logs

**Objetivo**: Implementar medidas de segurança e sistema de logs.

### 9.1 Segurança
- [ ] Proteção CSRF com tokens
- [ ] Validação e sanitização de inputs
- [ ] Headers de segurança
- [ ] Rate limiting básico

### 9.2 Sistema de Logs
- [ ] Logger baseado em arquivos
- [ ] Níveis de log (debug, info, warning, error)
- [ ] Rotação de logs
- [ ] Logs estruturados

### 9.3 Error Handling
- [ ] Handler global de erros
- [ ] Páginas de erro personalizadas
- [ ] Reporting de erros em produção

**Entregável**: Framework seguro com sistema de logs robusto.

---

## Etapa 10: Qualidade e Testes

**Objetivo**: Implementar ferramentas de qualidade de código e testes.

### 10.1 Configuração de Testes
- [ ] Setup PestPHP
- [ ] Estrutura de testes
- [ ] Helpers para testes

### 10.2 Análise Estática
- [ ] Configuração PHPStan
- [ ] Configuração PHP_CodeSniffer
- [ ] CI/CD básico

### 10.3 Documentação
- [ ] PHPDocumentor setup
- [ ] Documentação de APIs
- [ ] Guias de uso

**Entregável**: Framework com alta qualidade de código e testes.

---

## Etapa 11: Build e Deploy

**Objetivo**: Criar sistema de build para produção.

### 11.1 Comando Build
- [ ] `build` - otimização para produção
- [ ] Remoção de arquivos de desenvolvimento
- [ ] Autoload otimizado

### 11.2 Deploy
- [ ] Scripts de deploy
- [ ] Configuração de produção
- [ ] Documentação de deploy

### 11.3 Monitoramento
- [ ] Health checks
- [ ] Métricas básicas
- [ ] Alertas de erro

**Entregável**: Sistema completo pronto para produção.

---

## Etapa 12: Documentação e Polimento

**Objetivo**: Finalizar documentação e polir detalhes.

### 12.1 Documentação Completa
- [ ] README detalhado
- [ ] Guia de instalação
- [ ] Tutorial de uso
- [ ] Referência de APIs

### 12.2 Exemplos
- [ ] Aplicação de exemplo
- [ ] Casos de uso comuns
- [ ] Best practices

### 12.3 Polimento
- [ ] Refatoração final
- [ ] Otimizações finais
- [ ] Testes de integração

**Entregável**: Framework completo, documentado e pronto para uso.

---

## Critérios de Sucesso

- **Performance**: TTFB < 50ms em produção
- **Inodes**: < 200 arquivos na instalação fresh
- **Compatibilidade**: Funciona em hospedagem compartilhada
- **Usabilidade**: Curva de aprendizado suave
- **Segurança**: Seguir melhores práticas de segurança
- **Manutenibilidade**: Código limpo e bem documentado

## Estimativa de Tempo

- **Etapas 1-3**: 2-3 semanas (MVP funcional)
- **Etapas 4-6**: 2-3 semanas (Funcionalidades core)
- **Etapas 7-9**: 2-3 semanas (Frontend e segurança)
- **Etapas 10-12**: 1-2 semanas (Qualidade e documentação)

**Total estimado**: 7-11 semanas para framework completo.
