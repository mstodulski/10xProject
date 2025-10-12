# Post-Accident Vehicle Inspection Management System

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Status: In Development](https://img.shields.io/badge/Status-In%20Development-yellow.svg)]()

## Table of Contents
- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Getting Started Locally](#getting-started-locally)
- [Available Scripts](#available-scripts)
- [Project Scope](#project-scope)
- [Project Status](#project-status)
- [License](#license)

## Project Overview

The Post-Accident Vehicle Inspection Management System is a web application designed to efficiently manage the scheduling of post-accident vehicle inspections. The system allows consultants to book inspection appointments for clients while providing inspectors with a clear view of their schedule, eliminating the problem of overlapping appointments.

### Business Purpose

The primary goal is to eliminate scheduling conflicts by centralizing calendar management and synchronizing inspector availability across all consultants.

### Key Stakeholders

- **Consultants**: Service employees who handle client requests
- **Inspector**: Person conducting vehicle inspections
- **Clients**: People scheduling post-accident vehicle inspections

### Current Problem

Currently, clients schedule vehicle inspections through various communication channels (phone, email), contacting different consultants. With only one inspector available to conduct inspections, consultants have no way to check if a specific time slot is already booked by another client, leading to numerous scheduling conflicts (approximately 10 conflicts per week).

## Tech Stack

### Backend
- **Framework**: Symfony 7.3
- **Database**: MySQL
- **Testing**: PHPUnit

### Frontend
- **Libs**: NodeJS 22, npm 10.9
- **Framework**: Bootstrap
- **JavaScript Library**: FullCalendar.js
- **Design Approach**: Mobile-first design

### Deployment & Infrastructure
- **Hosting**: Cloud-based
- **Deployment**: Git + CI/CD with manual trigger
- **Availability**: Remote access 24/7

## Getting Started Locally

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 8.0 or higher
- Node.js and npm (for frontend assets)
- Git

### Installation Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/mstodulski/10xProject.git
   cd vehicle-inspection-system
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   ```

4. Configure your environment variables:
   ```bash
   cp .env .env.local
   ```
   Then edit `.env.local` with your database credentials and other configuration settings.

5. Create database:
   ```bash
   php bin/console doctrine:database:create
   ```

6. Run migrations:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

7. Load initial data:
   ```bash
   php bin/console doctrine:fixtures:load
   ```

8. Compile assets:
   ```bash
   npm run dev
   ```

9. Start the Symfony development server:
   ```bash
   symfony server:start
   ```

10. Access the application at `http://localhost:8000`

## Available Scripts

### Development

```bash
# Start the Symfony development server
symfony server:start

# Watch and compile assets
npm run watch

# Run tests
php bin/phpunit

# Run code quality tools
php vendor/bin/phpstan analyse src
php vendor/bin/php-cs-fixer fix
```

### Deployment

```bash
# Build assets for production
npm run build

# Clear cache
php bin/console cache:clear --env=prod

# Run migrations
php bin/console doctrine:migrations:migrate --env=prod
```

## Project Scope

### Included in MVP

- Calendar management system (create, edit, delete, view appointments)
- Simple user account system with two roles (consultant, inspector)
- Graphical calendar presentation with booked slots
- Validation of appointments according to business rules
- Responsive user interface

### Not Included in MVP

- Email or SMS notifications for inspectors about scheduled appointments
- Attaching reports or photos from inspections to events
- Marking events as completed
- Support for more than one inspector
- Two-factor authentication
- User password change functionality
- Automatic suggestion of available time slots
- Conflict resolution for simultaneous edits

### Technical Assumptions

- Inspection duration: 30 minutes
- Minimum time between inspections for documentation: 15 minutes
- Service working hours: 07:00 to 16:00
- Possible inspection start times: every 15 minutes from the full hour
- No inspections available on Saturdays and Sundays
- Appointments can be booked maximum 2 weeks in advance

## Project Status

The project is currently in the development phase. The following milestones are planned:

1. Requirements gathering and analysis - Completed
2. Design phase - In progress
3. Development of MVP - Not started
4. Testing phase - Not started
5. Deployment - Not started

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
