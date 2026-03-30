# Task Management API

A REST API built with Laravel 13 and MySQL for managing tasks with priority-based sorting, strict status transitions, and a daily report endpoint.

## Stack
- PHP 8.3
- Laravel 13
- MySQL 8

## Local Setup
```bash
git clone https://github.com/MumbiNdegwa28/Task-Management-API.git
cd Task-Management-API
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your MySQL credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=your_password
```
```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

API is available at `http://localhost:8000/api/`

## Deploy to Railway

1. Push repo to GitHub
2. Go to [railway.app](https://railway.app) → New Project → Deploy from GitHub
3. Add a **MySQL** plugin from the Railway dashboard
4. Set these environment variables in Railway → Variables:
```
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}
APP_KEY=        ← paste your local APP_KEY value
APP_ENV=production
```

5. Add `railway.json` to project root (already included in this repo)

## API Endpoints

### Create a task
```
POST /api/tasks
Content-Type: application/json

{
    "title": "Fix login bug",
    "due_date": "2026-04-05",
    "priority": "high"
}
```

### List tasks
```
GET /api/tasks
GET /api/tasks?status=pending
```

### Advance task status
```
PATCH /api/tasks/{id}/status
Content-Type: application/json

{
    "status": "in_progress"
}
```

### Delete a task (only done tasks)
```
DELETE /api/tasks/{id}
```

### Daily report
```
GET /api/tasks/report?date=2026-04-05
```

## Business Rules
- Title must be unique per due_date
- due_date must be today or in the future
- Status can only move forward: `pending → in_progress → done`
- Only `done` tasks can be deleted (returns `403` otherwise)

## Database
MySQL. Import the included dump file:
```bash
mysql -u root -p task_management < task_management_dump.sql
```