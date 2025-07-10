# Guia de Instalação - GuepardoSys Micro PHP

## 📋 Pré-requisitos

### Requisitos do Sistema
- **PHP**: 8.3 ou superior (recomendado 8.4)
- **Composer**: Para gerenciamento de dependências
- **Bun**: Para compilação de assets frontend (opcional)
- **Servidor Web**: Apache, Nginx ou servidor de desenvolvimento PHP
- **Banco de Dados**: MySQL 8.0+ ou PostgreSQL 12+ (opcional)

### Verificação do Ambiente
```bash
# Verificar versão do PHP
php -v

# Verificar extensões PHP necessárias
php -m | grep -E "(pdo|pdo_mysql|pdo_pgsql|json|mbstring)"

# Verificar Composer
composer --version

# Verificar Bun (opcional)
bun --version
```

## 🚀 Instalação Rápida

### 1. Download do Framework
```bash
# Via Git (recomendado)
git clone https://github.com/seu-usuario/micro-framework-php.git meu-projeto
cd meu-projeto

# OU via download direto
curl -L https://github.com/seu-usuario/micro-framework-php/archive/main.zip -o framework.zip
unzip framework.zip
cd micro-framework-php-main
```

### 2. Instalação de Dependências
```bash
# Instalar dependências PHP
composer install

# Instalar dependências frontend (opcional)
bun install
```

### 3. Configuração Básica
```bash
# Copiar arquivo de configuração
cp .env.example .env

# Configurar permissões (Linux/Mac)
chmod 755 guepardo
chmod -R 775 storage/
chmod -R 775 public/assets/
```

### 4. Servidor de Desenvolvimento
```bash
# Iniciar servidor
./guepardo serve

# Acessar em http://localhost:8000
```

## ⚙️ Configuração Detalhada

### Arquivo .env
```env
# Configuração da Aplicação
APP_NAME="Minha Aplicação"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Configuração de Banco de Dados (opcional)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=minha_base
DB_USERNAME=usuario
DB_PASSWORD=senha

# Configuração de Cache
CACHE_DRIVER=file
CACHE_TTL=3600

# Configuração de Logs
LOG_LEVEL=debug
LOG_MAX_FILES=5
```

### Banco de Dados (Opcional)

#### MySQL
```bash
# 1. Criar banco de dados
mysql -u root -p -e "CREATE DATABASE minha_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Configurar .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=minha_base
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# 3. Executar migrações
./guepardo migrate

# 4. Popular dados de exemplo (opcional)
./guepardo db:seed
```

#### PostgreSQL
```bash
# 1. Criar banco de dados
createdb minha_base

# 2. Configurar .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=minha_base
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# 3. Executar migrações
./guepardo migrate

# 4. Popular dados de exemplo (opcional)
./guepardo db:seed
```

### Frontend (Opcional)

#### Desenvolvimento
```bash
# Compilar assets para desenvolvimento
bun run dev

# Modo watch (recompila automaticamente)
bun run watch
```

#### Produção
```bash
# Compilar assets para produção (minificado)
bun run build

# Verificar saída
ls -la public/assets/
```

## 🌐 Configuração de Servidor Web

### Apache (.htaccess incluído)
O framework já inclui arquivo `.htaccess` configurado. Certifique-se de que:

```apache
# Habilitar mod_rewrite
sudo a2enmod rewrite

# Configurar VirtualHost (exemplo)
<VirtualHost *:80>
    ServerName meusite.local
    DocumentRoot /caminho/para/projeto/public
    
    <Directory /caminho/para/projeto/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx
```nginx
server {
    listen 80;
    server_name meusite.local;
    root /caminho/para/projeto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## 🔧 Hospedagem Compartilhada

### Estrutura para Shared Hosting
```
public_html/          # (documento root do servidor)
├── index.php         # Copiar de public/index.php
├── assets/           # Copiar de public/assets/
├── .htaccess         # Copiar de public/.htaccess
└── app/              # Todo o resto do framework
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── src/
    ├── vendor/
    └── ...
```

### Script de Deploy
```bash
#!/bin/bash
# deploy-shared.sh

# 1. Fazer upload dos arquivos
rsync -avz --exclude='.git' --exclude='node_modules' ./ usuario@servidor:~/public_html/app/

# 2. Mover arquivos públicos
ssh usuario@servidor "
cd ~/public_html
cp app/public/index.php ./
cp -r app/public/assets ./
cp app/public/.htaccess ./
"

# 3. Otimizar para produção
ssh usuario@servidor "cd ~/public_html/app && ./guepardo optimize"
```

## 📦 Criando um Novo Projeto

### Estrutura Inicial
```bash
# 1. Instalar framework
composer create-project guepardosys/micro-framework meu-projeto

# 2. Configurar
cd meu-projeto
cp .env.example .env
./guepardo optimize

# 3. Criar primeiro controller
./guepardo make:controller WelcomeController

# 4. Criar primeiro model (se usando BD)
./guepardo make:model Post

# 5. Iniciar desenvolvimento
./guepardo serve
```

### Primeiro Controller
```php
<?php
// app/Controllers/WelcomeController.php

namespace App\Controllers;

class WelcomeController extends BaseController
{
    public function index()
    {
        return $this->view('welcome', [
            'title' => 'Bem-vindo!',
            'message' => 'Seu projeto está funcionando!'
        ]);
    }
}
```

### Primeira Rota
```php
<?php
// routes/web.php

$router->get('/', 'WelcomeController@index');
```

## 🧪 Verificação da Instalação

### Testes Básicos
```bash
# Executar suite de testes
./guepardo test

# Verificar qualidade do código
./guepardo quality

# Verificar rotas
./guepardo route:list

# Verificar migrações (se usando BD)
./guepardo migrate:status
```

### Verificação Manual
1. **Servidor**: Acesse `http://localhost:8000`
2. **Assets**: Verifique se CSS/JS carregam
3. **Banco**: Teste operações CRUD se configurado
4. **Logs**: Verifique `storage/logs/` por erros

## 🔍 Troubleshooting

### Problemas Comuns

#### "Comando não encontrado: ./guepardo"
```bash
# Verificar permissões
ls -la guepardo
chmod +x guepardo

# Verificar shebang
head -1 guepardo  # Deve mostrar #!/usr/bin/env php
```

#### "Erro de conexão com banco"
```bash
# Verificar configurações
./guepardo config:show

# Testar conexão
php -r "
require 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;dbname=test', 'user', 'pass');
echo 'Conexão OK!';
"
```

#### "Assets não carregam"
```bash
# Recompilar assets
bun run build

# Verificar permissões
chmod -R 755 public/assets/

# Verificar .htaccess
cat public/.htaccess
```

#### "Views não são encontradas"
```bash
# Verificar permissões
chmod -R 755 app/Views/

# Limpar cache
./guepardo cache:clear

# Verificar cache de views
ls -la storage/cache/views/
```

## 🚀 Próximos Passos

Após instalação bem-sucedida:

1. **[Tutorial Básico](tutorial.md)** - Criar sua primeira aplicação
2. **[Referência API](api-reference.md)** - Documentação completa da API
3. **[Melhores Práticas](best-practices.md)** - Guia de boas práticas
4. **[Deployment](deployment.md)** - Deploy em produção

## 💡 Dicas Avançadas

### Performance
```bash
# Otimizar para produção
./guepardo optimize

# Cache de configurações
./guepardo config:cache

# Autoload otimizado
composer dump-autoload --optimize --classmap-authoritative
```

### Desenvolvimento
```bash
# Watch mode para assets
bun run watch &

# Servidor com hot reload
./guepardo serve --reload

# Debug mode
APP_DEBUG=true ./guepardo serve
```

### Manutenção
```bash
# Limpeza completa
./guepardo cache:clear
./guepardo optimize

# Logs de erro
tail -f storage/logs/error.log

# Backup antes de atualizações
tar -czf backup-$(date +%Y%m%d).tar.gz . --exclude=vendor --exclude=node_modules
```

---

**🎯 Instalação concluída! Pronto para desenvolver com GuepardoSys Micro PHP.**
