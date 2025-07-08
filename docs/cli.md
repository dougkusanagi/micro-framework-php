# GuepardoSys CLI Tool

A powerful command-line tool for GuepardoSys Micro PHP framework development.

## Installation

The CLI tool is included with the framework and available as `./guepardo` in the project root.

Make sure it's executable:
```bash
chmod +x guepardo
```

## Available Commands

### Development Server

Start the built-in PHP development server:

```bash
./guepardo serve [host] [port]
```

**Examples:**
```bash
./guepardo serve                    # localhost:8000
./guepardo serve localhost 8080     # localhost:8080
./guepardo serve 0.0.0.0 3000      # 0.0.0.0:3000
```

### Code Generation

#### Generate Controllers

Create a new controller with CRUD methods:

```bash
./guepardo make:controller <ControllerName>
```

**Examples:**
```bash
./guepardo make:controller User       # Creates UserController
./guepardo make:controller Product    # Creates ProductController
./guepardo make:controller BlogPost   # Creates BlogPostController
```

**Generated controller includes:**
- Full CRUD methods (index, create, store, show, edit, update, destroy)
- Proper namespacing
- Request handling
- Suggested view paths

#### Generate Models

Create a new model with basic CRUD operations:

```bash
./guepardo make:model <ModelName>
```

**Examples:**
```bash
./guepardo make:model User          # Creates User model (table: users)
./guepardo make:model Product       # Creates Product model (table: products)
./guepardo make:model BlogPost      # Creates BlogPost model (table: blog_posts)
```

**Generated model includes:**
- Extends BaseModel
- Proper table naming (auto-pluralized)
- Fillable fields template
- Validation method
- Timestamp handling
- Active records method

### Route Management

#### List All Routes

Display all registered routes in a formatted table:

```bash
./guepardo route:list
```

**Output includes:**
- HTTP Method
- Route Path
- Handler (Controller@method)
- Total route count

### Help

Show available commands and usage:

```bash
./guepardo help
```

## File Structure

The CLI tool creates files in the following locations:

```
app/
├── Controllers/          # Generated controllers
│   ├── BaseController.php
│   ├── UserController.php
│   └── ProductController.php
├── Models/              # Generated models
│   ├── User.php
│   └── Product.php
└── Views/               # Suggested view directories
    ├── users/
    └── products/

stubs/                   # Template files
├── controller.stub      # Controller template
└── model.stub          # Model template
```

## Stubs System

The CLI uses a stub system for code generation. Templates are stored in `stubs/` directory:

- `controller.stub` - Controller template
- `model.stub` - Model template

### Variables in Stubs

Stubs use `{{variable}}` syntax for replacements:

- `{{ControllerName}}` - Controller class name
- `{{ModelName}}` - Model class name
- `{{TableName}}` - Database table name
- `{{viewPath}}` - Suggested view path

## Examples

### Creating a Blog System

```bash
# Create blog post model
./guepardo make:model BlogPost

# Create blog controller
./guepardo make:controller BlogPost

# List routes to see what's available
./guepardo route:list

# Start development server
./guepardo serve
```

### Quick Development Setup

```bash
# Start server in background
./guepardo serve localhost 8000 &

# Create your resources
./guepardo make:model Product
./guepardo make:controller Product

# Check your routes
./guepardo route:list
```

## Command Output

The CLI provides color-coded output:

- 🟢 **Green** - Success messages
- 🔴 **Red** - Error messages  
- 🔵 **Blue** - Info messages
- 🟡 **Yellow** - Warning messages

## Error Handling

The CLI includes proper error handling:

- File existence checks
- Directory creation
- Template validation
- Clear error messages

## Future Commands

Planned commands for future versions:

- `migrate:up` - Run database migrations
- `migrate:down` - Rollback migrations
- `make:migration` - Create migration files
- `seed:run` - Run database seeds
- `cache:clear` - Clear view cache
- `config:cache` - Cache configuration
- `build` - Build for production

## Contributing

To add new commands:

1. Create a new command class in `src/CLI/Commands/`
2. Extend `BaseCommand`
3. Implement required methods
4. Register in `Console.php`
5. Add stub files if needed

## License

MIT License - Same as GuepardoSys Micro PHP framework.
