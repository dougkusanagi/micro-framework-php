Blueprint Técnico: GuepardoSys Micro PHP

Autor: Gemini
Versão: 1.0
Data: 07/07/2024
1. Arquitetura e Estrutura de Diretórios

Para minimizar o uso de inodes e manter a clareza, a seguinte estrutura de diretórios é proposta:

/guepardosys/
├── app/
│   ├── Controllers/
│   │   └── HomeController.php
│   ├── Models/
│   │   └── User.php
│   └── Views/
│       ├── layouts/
│       │   └── main.php
│       ├── pages/
│       │   └── home.php
│       └── partials/
│           └── header.php
├── bootstrap/
│   └── app.php         # Inicializa o app, autoload, env, container de dependências
├── config/
│   ├── app.php         # Configurações gerais (nome, url, etc)
│   └── database.php    # Configurações de conexão com o BD
├── database/
│   ├── migrations/
│   │   └── 2024_07_07_000000_create_users_table.sql
│   └── seeds/
│       └── users_seed.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css   # Arquivo CSS compilado pelo Tailwind
│   │   └── js/             # Para JS customizado, se necessário
│   ├── .htaccess           # Regras para Apache
│   └── index.php           # Entrypoint da aplicação
├── routes/
│   └── web.php             # Definição das rotas web
├── storage/
│   ├── cache/              # Views compiladas
│   └── logs/
│       └── app.log         # Logs da aplicação
├── vendor/                 # Dependências do Composer
├── .env.example            # Arquivo de exemplo para variáveis de ambiente
├── .env                    # Arquivo de configuração local (gitignored)
├── composer.json           # Dependências PHP
├── package.json            # Dependências e scripts frontend (Bun)
├── bun.lockb               # Lockfile do Bun
├── tailwind.config.js      # Configuração do Tailwind CSS
└── guepardo                # Script da CLI

2. Componentes Core
2.1. Entrypoint e Bootstrap (public/index.php e bootstrap/app.php)

    public/index.php:

        Define uma constante GUEPARDO_START com microtime(true).

        Requer o vendor/autoload.php do Composer.

        Requer o bootstrap/app.php.

        O bootstrap/app.php retorna uma instância da classe App.

        Chama um método run() na instância do app, que lida com a requisição e envia a resposta.

    bootstrap/app.php:

        Carrega as variáveis de ambiente do .env para $_ENV e $_SERVER usando uma implementação própria e leve.

        Cria uma instância de um Container de Injeção de Dependência (DI) simples para gerenciar serviços como o Router e a conexão com o BD.

        Registra as configurações dos arquivos em config/ no container.

        Inicializa e retorna a classe principal da aplicação.

2.2. Roteador (routes/web.php)

    Implementação: Uma classe Router simples.

    routes/web.php: Este arquivo não conterá lógica, apenas retornará um array de definições de rotas que será consumido pela classe Router.

    <?php // routes/web.php
    use App\Controllers\HomeController;

    return [
        ['GET', '/', [HomeController::class, 'index']],
        ['GET', '/blog/{slug}', [BlogController::class, 'show']],
        ['POST', '/contact', [ContactController::class, 'store']],
    ];

    Matching: O roteador obterá a URI e o método da requisição de $_SERVER. Ele percorrerá o array de rotas, usando uma expressão regular simples para corresponder a rotas com parâmetros (ex: preg_match('#^/blog/(?<slug>[^/]+)$#', $uri, $matches)).

    Despacho: Ao encontrar uma correspondência, o roteador instanciará o Controller e chamará o método correspondente, passando os parâmetros da rota.

2.3. Controllers e Models

    BaseController: Uma classe base opcional pode fornecer métodos de ajuda, como view() e redirect().

    Controllers: Classes Padrão (POPOs - Plain Old PHP Objects) que recebem dependências (como a classe Request) via construtor.

    BaseModel: Uma classe base para os models que conterá a instância do PDO (injetada) e métodos CRUD básicos (find, all, create, update, delete).

    // Exemplo de método em BaseModel
    public static function find(int $id) {
        $stmt = self::pdo()->prepare("SELECT * FROM " . self::getTable() . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

2.4. Motor de Template

Será uma classe View que implementa a lógica do template.

    Chamada: view('pages.home', ['name' => 'John']). O ponto . será traduzido para a barra de diretório /.

    Processo:

        O método view() verifica se existe uma versão compilada e válida no diretório storage/cache/.

        Se não houver, ele lê o arquivo .php do template de app/Views/.

        A classe usa uma série de substituições com preg_replace_callback e str_replace para converter as diretivas em PHP puro:

            {{ $name }} -> <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>

            @if(condition) -> <?php if(condition): ?>

            @endif -> <?php endif; ?>

            @extends('layouts.main'): Armazena o layout pai e continua a compilação.

            @section('content') ... @endsection: Captura o conteúdo da seção em uma variável.

        Após a compilação, o arquivo final (PHP puro) é salvo em storage/cache/ com um nome hasheado.

        O arquivo compilado é então incluído (require) dentro de um escopo isolado para renderizar a view com as variáveis passadas.

        A lógica de @yield é a última a ser resolvida, injetando o conteúdo das seções capturadas no layout pai.

2.5. CLI guepardo

Um único arquivo executável na raiz do projeto.

#!/usr/bin/env php
<?php
// guepardo

// Bootstrap mínimo para autoload e .env
require __DIR__ . '/vendor/autoload.php';
// ... carregar .env ...

$args = $argv;
$command = $args[1] ?? null;

switch ($command) {
    case 'serve':
        // Lógica do `php -S localhost:8000 -t public`
        break;
    case 'make:controller':
        $name = $args[2];
        // Lê um stub, substitui o nome e salva em app/Controllers
        break;
    case 'migrate:up':
        // Lê os arquivos .sql de database/migrations, executa no BD
        // e registra a migração em uma tabela `migrations` no banco.
        break;
    // ... outros comandos
    default:
        echo "Comando desconhecido.\n";
}

2.6. Configuração de Servidor

    Apache (public/.htaccess):

    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.php$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.php [L]
    </IfModule>

    Nginx (exemplo de nginx.conf):

    server {
        # ...
        root /caminho/para/guepardosys/public;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            # ... fastcgi_pass ...
        }
    }

2.7. Frontend Workflow

    package.json:

    {
      "scripts": {
        "dev": "tailwindcss -i ./resources/css/app.css -o ./public/assets/css/style.css --watch",
        "build": "tailwindcss -i ./resources/css/app.css -o ./public/assets/css/style.css --minify"
      },
      "devDependencies": {
        "tailwindcss": "^3.0"
      }
    }

    (Nota: Será necessário criar um diretório resources/css/app.css para o input do Tailwind).

    Uso:

        Desenvolvimento: Rodar bun run dev.

        Produção: Rodar bun run build antes do deploy.

    HTML (layouts/main.php):

    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GuepardoSys</title>
        <link href="/assets/css/style.css" rel="stylesheet">
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <!-- Outros CDNs (Lucide, Glide, Google Fonts) -->
    </head>
    <body>
        @yield('content')
    </body>
    </html>

