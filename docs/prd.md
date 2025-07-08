Documento de Requisitos do Produto (PRD): GuepardoSys Micro PHP

Autor: Gemini
Versão: 1.0
Data: 07/07/2024
1. Visão Geral e Objetivo

O GuepardoSys Micro PHP é um micro-framework PHP com arquitetura MVC (Model-View-Controller) projetado especificamente para ser leve, rápido e eficiente em ambientes de hospedagem compartilhada, onde os recursos, especialmente o número de arquivos e diretórios (inodes), são limitados.

O objetivo principal é fornecer uma alternativa moderna e minimalista aos frameworks full-stack como Laravel ou Symfony, focando nas funcionalidades essenciais para o desenvolvimento de sites institucionais e blogs, sem a sobrecarga de componentes complexos e um grande número de arquivos.
2. Público-Alvo

    Desenvolvedores PHP Freelancers e Pequenas Agências: Que criam sites para clientes em hospedagens compartilhadas (como Hostinger, Hostgator, etc.) e precisam de uma ferramenta produtiva, mas que respeite as limitações do ambiente.

    Desenvolvedores que preferem simplicidade: Que buscam controle total sobre o código e evitam a "mágica" de frameworks maiores para projetos de pequeno e médio porte.

    Estudantes e entusiastas de PHP: Que desejam entender os conceitos de um framework MVC sem a complexidade de uma base de código massiva.

3. Problema a ser Resolvido

Frameworks PHP populares são poderosos, mas trazem consigo desvantagens significativas para projetos mais simples e ambientes restritos:

    Alto Consumo de Inodes: Uma instalação padrão do Laravel pode facilmente exceder 20.000 arquivos, tornando-o inviável em muitos planos de hospedagem compartilhada.

    Curva de Aprendizagem: A vasta quantidade de funcionalidades pode ser esmagadora para desenvolvedores que precisam apenas do básico.

    Sobrecarga de Performance (Overhead): O carregamento de dezenas ou centenas de arquivos e serviços que não serão utilizados impacta a performance, especialmente o tempo de resposta inicial do servidor (TTFB).

O GuepardoSys ataca diretamente esses problemas, oferecendo um núcleo enxuto e um número de arquivos drasticamente reduzido.
4. Requisitos Funcionais
4.1. Core do Framework (Backend)

    Estrutura MVC: Organização clara de código em Models, Views e Controllers.

    Roteamento Simples:

        Deve permitir a definição de rotas (GET, POST, etc.) em um arquivo dedicado (routes/web.php).

        Suporte a parâmetros em rotas (ex: /blog/{slug}).

        Deve funcionar de forma transparente em servidores Apache (.htaccess) e Nginx.

    Controllers: Classes simples que recebem requisições, interagem com os Models e retornam uma View.

    Models: Camada de abstração para interação com o banco de dados utilizando PHP PDO, garantindo compatibilidade com MySQL, PostgreSQL, etc. As operações devem ser diretas (SQL puro ou com um query builder muito leve).

    Sistema de Template Simples:

        Um motor de template customizado, inspirado no Blade, mas ultra-leve.

        Deve suportar: {{ $variavel }}, @extends, @section, @yield, @include, e diretivas de controle (@if, @foreach, etc.).

        O motor deve compilar os templates em PHP puro e armazená-los em cache para máxima performance.

    Gerenciamento de Configuração:

        As configurações sensíveis e de ambiente (banco de dados, URL da aplicação) devem ser gerenciadas através de um arquivo .env.

    Autenticação Básica:

        Fornecer mecanismos para registro, login (com sessão) e logout de usuários.

    Cache Simples: Um sistema de cache baseado em arquivos para armazenar views compiladas e, opcionalmente, resultados de consultas de banco de dados.

    Logs: Sistema para registrar erros e eventos importantes em arquivos de log.

4.2. Ferramenta de Linha de Comando (guepardo)

Uma única ferramenta de linha de comando, php guepardo, para auxiliar no desenvolvimento.

    serve: Inicia um servidor de desenvolvimento local (php -S).

    make:controller {Nome}: Cria um arquivo de controller.

    make:model {Nome}: Cria um arquivo de model.

    route:list: Lista todas as rotas registradas na aplicação.

    migrate:up: Executa os arquivos SQL de migração que ainda não foram rodados.

    migrate:down: Reverte a última migração.

    migrate:seed: Executa os arquivos SQL para popular o banco de dados.

    build: Prepara a aplicação para produção, otimizando o autoload e removendo arquivos de desenvolvimento.

4.3. Frontend

    Gerenciador de Pacotes: Utilizar Bun para gerenciar dependências de frontend (Tailwind CSS) e como executor de scripts.

    Estilização: Tailwind CSS, instalado via Bun, com um script para compilar o CSS final.

    Interatividade: Alpine.js, incluído via CDN para simplicidade e leveza.

    Componentes: Glide.js (sliders) e Lucide Icons, ambos via CDN para minimizar o número de arquivos locais.

    Fontes: Google Fonts, carregadas via CDN.

4.4. Qualidade e Testes

    Autoload: Conformidade com o padrão PSR-4 gerenciado pelo Composer.

    Testes: Ambiente configurado para testes com PestPHP.

    Análise de Código Estático: Configuração para PHPStan para garantir a qualidade do código.

    Padrão de Código: Utilização do PHP_CodeSniffer para garantir um estilo de código consistente.

    Documentação: Código documentado seguindo padrões que permitam a geração de documentação com PHPDocumentor.

5. Requisitos Não-Funcionais

    Performance: O framework deve ter um tempo de resposta extremamente baixo. O objetivo é um "hello world" com TTFB inferior a 50ms em um ambiente de produção otimizado.

    Baixo Uso de Inodes: A estrutura de diretórios e o número de arquivos no core do framework devem ser mínimos. Uma instalação "fresh" não deve ultrapassar 200 arquivos.

    Facilidade de Deploy: O processo de deploy deve ser simples, idealmente consistindo em um git pull ou upload de arquivos via FTP, seguido por um composer install --no-dev -o e a configuração do .env.

    Segurança: Deve seguir as melhores práticas de segurança, como prevenção contra XSS (via escape de variáveis nas views), CSRF (com um helper para gerar tokens) e SQL Injection (através do uso de prepared statements do PDO).

6. Fora do Escopo

    ORM Complexo: Não haverá um ORM como Eloquent ou Doctrine. A interação com o banco de dados será mais direta.

    Sistema de Filas (Queues): Funcionalidades assíncronas estão fora de escopo.

    Broadcasting / WebSockets: Fora de escopo.

    Sistema de Pacotes/Módulos: O framework não terá um sistema próprio para adicionar pacotes, contando com o Composer para isso.

    Painel de Administração (Admin Panel): Não será fornecido um painel de administração pronto.