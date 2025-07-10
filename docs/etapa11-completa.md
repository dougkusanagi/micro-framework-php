# Etapa 11: Build e Deploy - Documentação

## Resumo da Implementação

A Etapa 11 foi **CONCLUÍDA** com sucesso, implementando um sistema completo de build e deploy para produção com as seguintes funcionalidades:

## 🚀 Comando Build

### Funcionalidades Implementadas:
- **Comando CLI**: `guepardo build`
- **Otimização de produção**: Remove arquivos de desenvolvimento
- **Autoload otimizado**: Executa `composer install --no-dev --optimize-autoloader`
- **Limpeza de cache**: Remove caches de desenvolvimento e teste
- **Configuração de produção**: Gera `.htaccess` e configurações otimizadas

### Como usar:
```bash
php guepardo build
```

### O que o comando faz:
1. Cria diretório `/build` limpo
2. Copia projeto excluindo arquivos de desenvolvimento
3. Otimiza autoload do Composer
4. Limpa todos os caches
5. Remove arquivos desnecessários em produção
6. Gera configuração de produção
7. Cria arquivo `build-info.json` com informações do build

## 📦 Sistema de Deploy

### Comando CLI Deploy
**Comando**: `guepardo deploy [target] [options]`

#### Alvos de Deploy Suportados:
1. **Local**: `guepardo deploy local --path=/var/www/html`
2. **FTP**: `guepardo deploy ftp --host=servidor.com --username=user`
3. **Rsync**: `guepardo deploy rsync --destination=user@servidor.com:/var/www`

#### Opções Disponíveis:
- `--backup`: Criar backup antes do deploy
- `--path=PATH`: Caminho de destino para deploy local
- `--host=HOST`: Servidor FTP/SSH
- `--username=USER`: Usuário FTP/SSH
- `--password=PASS`: Senha FTP
- `--port=PORT`: Porta FTP/SSH
- `--remote-path=PATH`: Caminho remoto
- `--passive`: Usar modo passivo FTP

### Scripts PHP Standalone

#### 1. Script de Deploy (`scripts/deploy.php`)
Script PHP independente que pode ser usado sem o CLI:

```bash
# Deploy local
php scripts/deploy.php local /var/www/html

# Deploy FTP
php scripts/deploy.php ftp servidor.com usuario senha /public_html
```

**Funcionalidades**:
- Deploy local com backup automático
- Upload via FTP com suporte a modo passivo
- Deploy via Rsync
- Configuração de permissões automática
- Tarefas pós-deploy (limpeza de cache, etc.)
- Gestão de backups (mantém apenas os 5 mais recentes)

#### 2. Configuração de Deploy (`scripts/deploy-config.php`)
Arquivo de configuração centralizando todas as opções de deploy:

**Configurações incluídas**:
- Diretórios de build e backup
- Padrões de exclusão de arquivos
- Diretórios que precisam ser escritáveis
- Comandos pós-deploy
- Configurações por ambiente (produção, staging, local)
- Configurações FTP/Rsync específicas
- Health checks pós-deploy
- Configurações de notificação
- Configurações de rollback
- Configurações de segurança

## 🔍 Sistema de Monitoramento

### Comando Health Check
**Comando**: `guepardo health`

Executa verificações completas de saúde da aplicação:

#### Verificações Realizadas:
1. **Requisitos do Sistema**:
   - Versão do PHP (8.1+)
   - Extensões obrigatórias (PDO, mbstring, json, openssl, hash)
   - Limite de memória
   - Espaço em disco

2. **Conectividade de Banco**:
   - Teste de conexão
   - Execução de queries básicas
   - Tempo de resposta

3. **Sistema de Cache**:
   - Acessibilidade dos diretórios
   - Teste de escrita/leitura

4. **Permissões de Storage**:
   - Verificação de diretórios escritáveis
   - Permissões corretas

5. **Configurações de Segurança**:
   - Arquivos sensíveis não expostos
   - Configurações de erro em produção

6. **Métricas de Performance**:
   - Tempo de bootstrap da aplicação
   - Status do OPcache

### Script de Health Check Standalone (`scripts/health-check.php`)

Script PHP independente para monitoramento:

```bash
# Formato console (padrão)
php scripts/health-check.php

# Formato JSON
php scripts/health-check.php json

# Formato HTML
php scripts/health-check.php html
```

**Funcionalidades**:
- Múltiplos formatos de saída (console, JSON, HTML)
- Sistema de pontuação de saúde (0-100%)
- Classificação de criticidade dos problemas
- Log automático dos resultados
- Configuração personalizável via `scripts/health-config.php`
- Suporte a verificações customizadas
- Detecção de modo de manutenção

#### Configuração de Health Check (`scripts/health-config.php`)
Configuração detalhada para personalizar as verificações:

**Configurações incluídas**:
- Quais verificações executar
- Limites e thresholds
- Formato de saída
- Configurações de alerta (email, Slack)
- Intervalos de monitoramento
- Verificações específicas por categoria
- Endpoints customizados para teste
- Dados históricos
- Verificações customizadas

## 📊 Métricas e Alertas

### Sistema de Pontuação
- **0-59%**: Estado Crítico ❌
- **60-79%**: Estado de Alerta ⚠️
- **80-100%**: Estado Saudável ✅

### Criticidade dos Problemas
- **Críticos**: Problemas que impedem funcionamento (PHP version, extensões obrigatórias, DB connection)
- **Avisos**: Problemas que afetam performance mas não impedem funcionamento

### Logs e Histórico
- Logs automáticos em `storage/logs/health-check-YYYY-MM-DD.log`
- Histórico de verificações para análise de tendências
- Alertas com rate limiting para evitar spam

## 🛠️ Arquivos Criados/Modificados

### Novos Comandos CLI:
- `src/CLI/Commands/BuildCommand.php`
- `src/CLI/Commands/DeployCommand.php`
- `src/CLI/Commands/HealthCommand.php`

### Scripts Standalone:
- `scripts/deploy.php`
- `scripts/deploy-config.php`
- `scripts/health-check.php`
- `scripts/health-config.php`

### Modificações:
- `src/CLI/Console.php` - Registrou novos comandos

## 🎯 Critérios de Sucesso Atendidos

✅ **Sistema de Build**: Otimização completa para produção  
✅ **Deploy Automatizado**: Múltiplas opções (local, FTP, rsync)  
✅ **Scripts PHP**: Sem dependência de bash/shell scripts  
✅ **Monitoramento**: Health checks completos e configuráveis  
✅ **Configuração**: Altamente configurável e customizável  
✅ **Compatibilidade**: Funciona em hospedagem compartilhada  
✅ **Usabilidade**: Comandos simples e intuitivos  

## 🔄 Próximos Passos

A **Etapa 12: Documentação e Polimento** agora pode ser iniciada, focando em:
- README detalhado
- Guia de instalação
- Tutorial de uso
- Aplicação de exemplo
- Polimento final do código

A base técnica está completamente implementada e pronta para produção! 🚀
