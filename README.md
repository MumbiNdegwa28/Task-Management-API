# Task Management API

A REST API built with Laravel 13 and MySQL for managing tasks with priority-based sorting, strict status transitions, and a daily report endpoint.

## Live URL
```
https://task-management-api-production-a0e2.up.railway.app
```

## Stack
- PHP 8.3 / Laravel 13
- MySQL 8
- Hosted on Railway

## Database
MySQL. Import the included dump file:
```bash
mysql -u root -p task_management < task_management_dump.sql
```

## Local Setup
```bash
git clone https://github.com/MumbiNdegwa28/Task-Management-API.git
cd Task-Management-API
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
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

API runs at `http://localhost:8000/api/`

## Deploy to Railway

1. Push repo to GitHub
2. Go to [railway.app](https://railway.app) → New Project → Deploy from GitHub
3. Add a **MySQL** plugin
4. Set these variables on the app service using values from the MySQL plugin Connect tab:
```
DB_CONNECTION=mysql
DB_HOST=         - from MySQL service Connect tab
DB_PORT=         - from MySQL service Connect tab
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=     - from MySQL service Connect tab
APP_KEY=         - paste your local APP_KEY value
APP_ENV=production
APP_DEBUG=false
```

## API Endpoints

### 1. Create a task
```
POST https://task-management-api-production-a0e2.up.railway.app/api/tasks
Content-Type: application/json

{
    "title": "Fix login bug",
    "due_date": "2026-04-10",
    "priority": "high"
}
```
Response `201`:
```json
{
    "data": {
        "id": 1,
        "title": "Fix login bug",
        "due_date": "2026-04-10",
        "priority": "high",
        "status": "pending",
        "created_at": "2026-04-01T00:00:00.000000Z",
        "updated_at": "2026-04-01T00:00:00.000000Z"
    }
}
```

### 2. List all tasks
```
GET https://task-management-api-production-a0e2.up.railway.app/api/tasks
```
Sorted by priority (high - medium - low), then due_date ascending.

Response `200`:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Fix login bug",
            "due_date": "2026-04-10",
            "priority": "high",
            "status": "pending"
        }
    ]
}
```

### 3. List tasks filtered by status
```
GET https://task-management-api-production-a0e2.up.railway.app/api/tasks?status=pending
GET https://task-management-api-production-a0e2.up.railway.app/api/tasks?status=in_progress
GET https://task-management-api-production-a0e2.up.railway.app/api/tasks?status=done
```

### 4. Advance task status
```
PATCH https://task-management-api-production-a0e2.up.railway.app/api/tasks/{id}/status
Content-Type: application/json

{
    "status": "in_progress"
}
```
Status can only move forward: `pending → in_progress → done`. Cannot skip or revert.

Response `200`:
```json
{
    "data": {
        "id": 1,
        "title": "Fix login bug",
        "status": "in_progress"
    }
}
```

Invalid transition response `422`:
```json
{
    "message": "Invalid status transition.",
    "current": "pending",
    "allowed": "in_progress",
    "provided": "done"
}
```

### 5. Delete a task
```
DELETE https://task-management-api-production-a0e2.up.railway.app/api/tasks/{id}
```
Only tasks with status `done` can be deleted.

Response `200`:
```json
{
    "message": "Task deleted successfully."
}
```

Attempting to delete a non-done task returns `403`:
```json
{
    "message": "Forbidden. Only tasks with status \"done\" can be deleted."
}
```

### 6. Daily report (bonus)
```
GET https://task-management-api-production-a0e2.up.railway.app/api/tasks/report?date=2026-04-10
```
Returns task counts grouped by priority and status for the given date.

Response `200`:
```json
{
    "date": "2026-04-10",
    "summary": {
        "high":   {"pending": 2, "in_progress": 1, "done": 0},
        "medium": {"pending": 1, "in_progress": 0, "done": 3},
        "low":    {"pending": 0, "in_progress": 0, "done": 1}
    }
}
```

## Business Rules

- `title` must be unique per `due_date` — same title on different dates is allowed
- `due_date` must be today or in the future
- `priority` must be one of: `low`, `medium`, `high`
- Status transitions are strictly one-way: `pending → in_progress → done`
- Skipping or reverting status returns `422`
- Only tasks with status `done` can be deleted — returns `403` otherwise

## Evaluation Notes

- Business rules enforced via Laravel validation and Eloquent model logic
- `CASE` expression used for cross-database compatible priority sorting
- Status transition logic lives in the `Task` model via `nextStatus()` method
- All responses return consistent JSON regardless of request headers
- Migration includes a composite unique index on `(title, due_date)`