# GuepardoSys Micro PHP Framework

## 🚀 MVP Funcional

Este é o **Minimum Viable Product (MVP)** do GuepardoSys Micro PHP Framework - um micro-framework PHP leve e eficiente projetado especificamente para ambientes de hospedagem compartilhada.

## ✨ Funcionalidades Implementadas (Etapa 1)

- ✅ **Arquitetura MVC**: Estrutura organizada em Models, Views e Controllers
- ✅ **Roteamento Simples**: Sistema de rotas com suporte a parâmetros
- ✅ **Views Básicas**: Sistema de templates PHP puro
- ✅ **Autoload PSR-4**: Carregamento automático de classes via Composer
- ✅ **Configuração de Ambiente**: Suporte a arquivos `.env`
- ✅ **Servidor Web**: Configuração para Apache e Nginx

## 🛠️ Requisitos

- PHP 8.4 ou superior
- Composer
- Servidor web (Apache/Nginx) ou servidor de desenvolvimento PHP

## 📦 Instalação

1. Clone o repositório:
```bash
git clone <repository-url>
cd micro-framework-php
```

2. Instale as dependências:
```bash
composer install
```

3. Configure o ambiente:
```bash
cp .env.example .env
```

4. Inicie o servidor de desenvolvimento:
```bash
php -S localhost:8000 -t public
```

5. Acesse http://localhost:8000 no seu navegador

## 📁 Estrutura do Projeto

```
/
├── app/
│   ├── Controllers/     # Controllers da aplicação
│   ├── Models/          # Models da aplicação  
│   └── Views/           # Templates e views
├── bootstrap/           # Inicialização da aplicação
├── config/              # Arquivos de configuração
├── public/              # Ponto de entrada público
├── routes/              # Definição de rotas
├── src/                 # Core do framework
└── storage/             # Cache e logs
```

## 🔄 Rotas Disponíveis

- `GET /` - Página inicial
- `GET /about` - Página sobre

## 🎯 Próximas Etapas

- **Etapa 2**: Sistema de Templates Customizado (Blade-like)
- **Etapa 3**: Banco de Dados e Models
- **Etapa 4**: CLI Tool (guepardo)
- **Etapa 5**: Sistema de Migrações
- E muito mais...

## 📊 Performance

- **Arquivos**: < 50 arquivos no core (objetivo: < 200)
- **Inicialização**: Extremamente rápida
- **Memória**: Baixo consumo de recursos

## 🎯 Características Principais

- **Zero Dependencies**: Framework com dependências mínimas para runtime
- **Hospedagem Compartilhada**: Otimizado para ambientes restritivos
- **Implementação Custom**: Sistema próprio de environment variables
- **Performance**: TTFB < 50ms em produção
- **Simplicidade**: Curva de aprendizado suave
- **CLI Tool**: Ferramenta de linha de comando para desenvolvimento

## 🔧 Tecnologias

- **PHP**: 8.3+ (compatível com 8.4)
- **Composer**: Gerenciamento de dependências
- **Environment**: Sistema próprio de variáveis de ambiente (zero dependencies)
- **Templates**: Motor de templates inspirado no Blade
- **Database**: PDO com suporte a MySQL e PostgreSQL

## 🤝 Contribuição

Este projeto está em desenvolvimento ativo. Consulte o arquivo `/docs/plan.md` para ver o plano completo de implementação.

## 📄 Licença

MIT License - veja o arquivo LICENSE para detalhes.
