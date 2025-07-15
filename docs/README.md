# GuepardoSys Micro PHP Framework Documentation

Welcome to the comprehensive documentation for GuepardoSys Micro PHP Framework - a lightweight, powerful PHP framework designed specifically for shared hosting environments.

## 📚 Documentation Sections

### Getting Started
- [Installation Guide](getting-started/installation.md) - Complete installation and setup guide
- [Configuration](getting-started/configuration.md) - Environment and application configuration
- [Directory Structure](getting-started/structure.md) - Understanding the framework structure
- [Quick Start](getting-started/quickstart.md) - Build your first application

### Architecture Concepts
- [Request Lifecycle](architecture/lifecycle.md) - How requests flow through the framework
- [Service Container](architecture/container.md) - Dependency injection and service resolution
- [Facades](architecture/facades.md) - Static proxy interfaces to services
- [Providers](architecture/providers.md) - Service registration and bootstrapping

### The Basics
- [Routing](basics/routing.md) - URL routing and route parameters
- [Middleware](basics/middleware.md) - HTTP middleware and request filtering
- [Controllers](basics/controllers.md) - Handling HTTP requests
- [Requests](basics/requests.md) - Working with HTTP requests
- [Responses](basics/responses.md) - Returning HTTP responses
- [Views](basics/views.md) - Template engine and view rendering
- [Session](basics/session.md) - Session management
- [Validation](basics/validation.md) - Input validation
- [Error Handling](basics/errors.md) - Exception handling and debugging

### Database
- [Getting Started](database/getting-started.md) - Database configuration and connections
- [Query Builder](database/queries.md) - Fluent query building interface
- [Models](database/models.md) - Object-relational mapping (ORM)
- [Migrations](database/migrations.md) - Database schema versioning
- [Seeding](database/seeding.md) - Database seeding and test data

### Security
- [Authentication](security/authentication.md) - User authentication system
- [Authorization](security/authorization.md) - User authorization and permissions
- [CSRF Protection](security/csrf.md) - Cross-site request forgery protection
- [Encryption](security/encryption.md) - Data encryption and hashing
- [Hashing](security/hashing.md) - Password hashing

### Frontend
- [Asset Compilation](frontend/compilation.md) - Compiling CSS and JavaScript
- [Tailwind CSS](frontend/tailwind.md) - Utility-first CSS framework integration
- [Alpine.js](frontend/alpine.md) - Lightweight JavaScript framework

### Testing
- [Getting Started](testing/getting-started.md) - Introduction to testing
- [HTTP Tests](testing/http.md) - Testing HTTP endpoints
- [Database Testing](testing/database.md) - Testing with databases
- [Mocking](testing/mocking.md) - Mocking dependencies

### CLI Tool (Guepardo)
- [Introduction](cli/introduction.md) - Command-line interface overview
- [Writing Commands](cli/commands.md) - Creating custom CLI commands
- [Code Generation](cli/generators.md) - Generating boilerplate code

### Advanced Topics
- [Performance](advanced/performance.md) - Performance optimization techniques
- [Caching](advanced/caching.md) - Application caching strategies
- [Logging](advanced/logging.md) - Application logging and monitoring
- [Error Debugging](advanced/debugging.md) - Advanced error debugging system

### Deployment
- [Shared Hosting](deployment/shared-hosting.md) - Deploying to shared hosting
- [VPS Deployment](deployment/vps.md) - Virtual private server deployment
- [Optimization](deployment/optimization.md) - Production optimization

### API Reference
- [Core Classes](api/core.md) - Framework core classes
- [Helper Functions](api/helpers.md) - Global helper functions
- [Configuration](api/config.md) - Configuration reference

## 🚀 Framework Features

### ✅ Complete Implementation
- **MVC Architecture** - Clean separation of concerns
- **Advanced Routing** - Parameter binding and middleware support
- **Template Engine** - Blade-inspired templating with caching
- **Database ORM** - Eloquent-style model relationships
- **CLI Tool** - 20+ commands for development workflow
- **Authentication** - Complete user authentication system
- **Advanced Error Debugging** - Laravel Ignition-style error pages
- **Asset Pipeline** - Tailwind CSS and Alpine.js integration
- **Testing Suite** - PestPHP integration with comprehensive tests

### 🎯 Performance Metrics
- **TTFB**: < 30ms (6x faster than Laravel)
- **Files**: 171 total files (vs 20,000+ in Laravel)
- **Memory**: Ultra-low memory footprint
- **Compatibility**: 100% shared hosting compatible

### 🛠️ Technology Stack
- **Backend**: PHP 8.3+, PDO, PSR-4 Autoloading
- **Frontend**: Tailwind CSS, Alpine.js, Bun
- **Testing**: PestPHP, PHPStan, PHPCS
- **Database**: MySQL 8.0+, PostgreSQL 12+

## 📖 Quick Navigation

### For Beginners
1. [Installation Guide](getting-started/installation.md)
2. [Quick Start Tutorial](getting-started/quickstart.md)
3. [Basic Routing](basics/routing.md)
4. [Creating Controllers](basics/controllers.md)

### For Experienced Developers
1. [Architecture Overview](architecture/lifecycle.md)
2. [Advanced Routing](basics/routing.md#advanced-routing)
3. [Database Models](database/models.md)
4. [CLI Commands](cli/introduction.md)

### For DevOps
1. [Deployment Guide](deployment/shared-hosting.md)
2. [Performance Optimization](deployment/optimization.md)
3. [Monitoring and Logging](advanced/logging.md)

## 🤝 Contributing

This framework is production-ready and feature-complete. Contributions are welcome in the following areas:

- **Documentation**: Expanding examples and tutorials
- **Testing**: Additional test coverage
- **Performance**: Micro-optimizations
- **Features**: New CLI commands and utilities

## 📄 License

GuepardoSys Micro PHP Framework is open-sourced software licensed under the [MIT license](../LICENSE).

---

**⭐ If this framework helps you, please consider giving it a star on GitHub!**