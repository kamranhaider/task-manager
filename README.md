<<<<<<< HEAD
# task-manager
=======
# Task Manager — Laravel Full-Stack Assessment Starter

A small CRUD Task Manager using Laravel, Eloquent, MySQL, Blade, Bootstrap, and vanilla JavaScript.

## Features
- Responsive task list
- Add, edit, delete tasks
- Mark tasks completed/incomplete
- REST API: GET, POST, PUT/PATCH, DELETE
- MySQL persistence and server-side validation

## Setup
1. Create a Laravel application (Laravel 8+):
   ```bash
   composer create-project laravel/laravel task-manager
   ```
2. Copy the files from this package into the matching paths in your Laravel project.
3. Configure `.env` with your MySQL database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=task_manager
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```
4. Create the database in MySQL, then run:
   ```bash
   php artisan migrate
   php artisan serve
   ```
5. Open the URL shown by `php artisan serve`.

## API endpoints
| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/tasks` | List tasks |
| POST | `/api/tasks` | Create a task |
| GET | `/api/tasks/{task}` | Get one task |
| PUT/PATCH | `/api/tasks/{task}` | Update a task |
| DELETE | `/api/tasks/{task}` | Delete a task |

### Example create request
```json
{
  "title": "Prepare interview demo",
  "description": "Finish and test the task manager",
  "is_completed": false
}
```

## Assessment notes
- `title` is required and limited to 255 characters.
- `description` is optional.
- `is_completed` is a boolean.
- The API returns JSON and appropriate HTTP status codes.
- Before submission, test all CRUD actions and take screenshots or record a short demo if requested.
>>>>>>> ccadcd6 (initialize assessment project)
