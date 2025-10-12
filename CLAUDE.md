# CLAUDE.md

The Post-Accident Vehicle Inspection Management System is a web application designed for efficient scheduling and 
management of post-accident vehicle inspections. The system allows consultants to book inspection appointments for clients, 
while inspectors can view their schedules, eliminating the issue of overlapping visits.

## Project Overview

This project is a Symfony 7.3-based web application for managing 

**Key Technologies:**
- PHP 8.4+
- Symfony 7.3 (with Doctrine ORM 3.5)
- MySQL 8.x
- Webpack Encore for frontend asset compilation
- jQuery, Bootstrap 5, FullCalendar

## Development Commands

### Testing
```bash
# Run all unit tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit tests/Tests.php

# Run PHPUnit with memory limit
php -d memory_limit=-1 vendor/bin/phpunit

# Static analysis with Psalm
vendor/bin/psalm
```

### Database Operations
```bash
# Run migrations
php bin/console doctrine:migrations:migrate

# Validate database schema
php bin/console doctrine:schema:validate

# Show schema changes (don't apply)
php bin/console doctrine:schema:update --dump-sql

# Generate migration from entity changes
php bin/console doctrine:migrations:diff

# Manually create migration
php bin/console doctrine:migrations:generate

```

### Frontend Build
```bash
# Install dependencies
npm install
# Generate routing files for frontend
php bin/console fos:js-routing:dump --format=json --target=public/js/fos_js_routes.json
# Compile JS translations
php bin/console bazinga:js-translation:dump public
# Production build
npm run build
```

### System Maintenance
```bash
# Clear cache
php bin/console cache:clear
``` 

## Architecture Overview

### Entity Structure

- **Entities** (`src/Entity/`): Doctrine ORM entities with attributes mapping

### Controllers & Forms

- **Controllers** (`src/Controller/`): Traditional Symfony controllers for web interface
- **Forms** (`src/Form/`): Symfony form types with filter types for search/filtering

### Services

Key services in `src/Service/`:
- **AppContextService**: Context management


### Frontend Structure

- **Webpack Encore** configuration in `webpack.config.js`
- Multiple entry points for different pages/features
- **FOSJsRoutingBundle**: Exposes Symfony routes to JavaScript
- **BazingaJsTranslationBundle**: Translation support in JavaScript
- Assets in `assets/` directory with organized JS files per feature

## Code Quality

- **Psalm**: Static analysis configured, ignoring Commands and DataFixtures
- **PHPUnit**: Configured test suites for entities, services, validators, types, etc.
- **Doctrine Validation**: Always validate schema after entity changes

## File Organization

- `src/Controller/`: Web controllers
- `src/Entity/`: Doctrine entities
- `src/EventListener/`: Symfony event listeners
- `src/Factory/`: Object factories
- `src/Form/`: Form types and filters
- `src/Repository/`: Doctrine repositories
- `src/Service/`: Business logic services
- `src/Validator/`: Custom validators
- `templates/`: Twig templates
- `translations/`: Translation files (organized by domain)
- `assets/`: Frontend assets (JS, CSS, images)
- `public/build/`: Compiled frontend assets (generated)
- `migrations/`: Database migrations

## Coding practices

### Guidelines for clean code

- Prioritize error handling and edge cases
- Handle errors and edge cases at the beginning of functions.
- Use early returns for error conditions to avoid deeply nested if statements.
- Place the happy path last in the function for improved readability.
- Avoid unnecessary else statements; use if-return pattern instead.
- Use guard clauses to handle preconditions and invalid states early.
- Implement proper error logging and user-friendly error messages.
- Consider using custom error types or error factories for consistent error handling.
- Always use translations in messages and twig templates
- Always use subdirectories in the translations directory to create translation files for each controller.
- Always use subdirectories in the templates directory to create templates for individual controllers.
- In templates, always extend the /templates/base.html.twig template (except templates loaded by ajax).

## Who you are

You are a world-class expert in the technologies listed in this document’s technical stack. Your role is to assist in 
the development of this project to ensure it meets all requirements, operates reliably, and remains easy to expand and 
maintain in the future.
Always respond in Polish.
