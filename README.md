# Team Task Manager

A small team task management application with a Laravel API backend and a Vue 3 SPA frontend.

## Tech Stack

- Backend: PHP 8.3+, Laravel 11, Laravel Sanctum, MySQL
- Frontend: Vue 3, TypeScript, Vite, Vuetify 3, Pinia, Axios

## Requirements

- PHP `^8.3`
- Composer
- MySQL
- Node.js `^22.18.0` or `>=24.12.0`
- npm

## Backend Setup

1. Go to the backend directory:

```bash
cd backend
```

2. Install PHP dependencies:

```bash
composer install
```

3. Create the environment file:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

4. Update the database settings in `backend/.env` if needed:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=team_task_manager
DB_USERNAME=root
DB_PASSWORD=root
```

5. Create the MySQL database:

```sql
CREATE DATABASE team_task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. Generate the Laravel application key:

```bash
php artisan key:generate
```

7. Run migrations and seed the test data:

```bash
php artisan migrate --seed
```

8. Start the backend API:

```bash
php artisan serve
OR
composer serve:local
```

The backend runs by default at:

```text
http://localhost:8000
```

API base URL:

```text
http://localhost:8000/api
```

## Frontend Setup

1. Open a new terminal and go to the frontend directory:

```bash
cd frontend
```

2. Install JavaScript dependencies:

```bash
npm install
```

3. Start the frontend development server:

```bash
npm run dev
```

The frontend runs at the URL printed by Vite, usually:

```text
http://localhost:5173
```

The frontend currently calls the backend directly at:

```text
http://localhost:8000/api
```

## Test Accounts

The following accounts are created by `backend/database/seeders/DatabaseSeeder.php`:

| ID  | Role  | Email               | Password       |
| --- | ----- | ------------------- | -------------- |
| 1   | admin | `admin@example.com` | `Admin@123456` |
| 2   | user  | `user1@example.com` | `User1@123456` |
| 3   | user  | `user2@example.com` | `User2@123456` |

Use `assigned_to = 2` to assign a task to User1, and `assigned_to = 3` to assign a task to User2.

## Quick Checks

Backend tests:

```bash
cd backend
php artisan test
```

Frontend checks:

```bash
cd frontend
npm run type-check
npm run test:unit:run
```

Frontend production build:

```bash
cd frontend
npm run build
```

## API Notes

- `POST /api/login`: logs in and returns a Sanctum bearer token.
- `GET /api/me`: returns the current authenticated user.
- `POST /api/logout`: deletes the current access token.
- `GET /api/users`: returns the assignable seeded users for admin users.
- `GET /api/tasks`: returns tasks and supports filtering by `status` and `assigned_to`.
- `POST /api/tasks`: creates a task.
- `PUT/PATCH /api/tasks/{task}`: updates a task.
- `DELETE /api/tasks/{task}`: deletes a task.

## Assumptions and Simplifications

- Authentication uses Laravel Sanctum personal access tokens as Bearer tokens; refresh tokens, token rotation, and detailed token expiration rules are not implemented.
- The frontend stores the token in `localStorage` to keep the demo simple; in production, an httpOnly cookie or another safer storage strategy should be considered.
- Registration, forgot password, email verification, and user management are out of scope.
- The application has only two roles: `admin` and `user`.
- Admin users can view and manage all tasks; regular users can only view and manage tasks assigned to themselves.
- Task deletion is currently hard delete via `delete`; soft deletes are not implemented.
- `GET /api/tasks` currently returns all matching records with `get()`; this is fine for demo data but not optimized for large datasets.
- Assignee options are loaded from the backend users API and limited to the seeded regular users: User1 (`id = 2`) and User2 (`id = 3`).
- Backend filtering currently focuses on `status` and `assigned_to`; search and pagination are intentionally kept simple for the 2-3 hour project scope.
- Advanced CORS hardening, advanced rate limiting, audit logs, and production observability are not fully configured.
- The seeder creates only a minimal dataset for testing login, authorization, and task CRUD flows.
