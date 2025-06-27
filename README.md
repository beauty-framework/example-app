# 📋 Example Application for Beauty Framework

This is an example of an application on Beauty Framework. This example implements authorization, simple CRUD for task management, as well as saving logs on task changes and Job upon user registration completion. In other words, it is a basic ToDo list created to familiarize yourself with the framework.

Since the framework is still raw, repositories with raw queries are used instead of the ORM module, and a custom command is used instead of migrations. However, all missing modules will be added in the future.

---

## 📦 Installation

```bash
git clone https://github.com/beauty-framework/example-app beauty-example-app
```

```bash
cd beauty-example-app
```

```bash
cp .env.example .env
```

```bash
make up # or make prod
```

```bash
make beauty migrate
```

---

## 📘 REST API

All routes require JSON bodies and respond with JSON. Authorization via `Bearer <token>` header is required unless explicitly stated.

### 🔐 Authentication

#### POST `/api/auth/login`

**Description**: Authenticate a user and return access token.

* **Body:**

```json
{ "email": "email", "password": "password" }
```

* **Response:**

```json
{
  "token": "...",
  "name": "Kirill",
  "email": "admin@admin.com"
}
```

* **Auth:** ❌ None

#### POST `/api/auth/register`

**Description**: Register a new user and return access token.

* **Body:**

```json
{ "name": "name", "email": "email", "password": "password" }
```

* **Response:** same as `/login`
* **Auth:** ❌ None

---

### ✅ Todos CRUD

#### GET `/api/todos`

**Description**: Get all todos for the authenticated user.

* **Response:**

```json
{
  "todos": [
    {
      "id": 1,
      "title": "title",
      "description": "description",
      "completed": true,
      "due_date": "2022-01-01 00:00:00"
    }
  ]
}
```

* **Auth:** ✅ `Bearer <token>`

#### GET `/api/todo/:id`

**Description**: Get specific todo by ID.

* **Response:**

```json
{
  "id": 1,
  "title": "title",
  "description": "description",
  "completed": true,
  "due_date": "2022-01-01 00:00:00"
}
```

* **Auth:** ✅ `Bearer <token>`

#### POST `/api/todo`

**Description**: Create a new todo.

* **Body:**

```json
{
  "title": "title",
  "description": "description",
  "completed": true,
  "due_date": "2022-01-01 00:00:00"
}
```

* **Response:** Same as body, plus `id` and `created_at`
* **Auth:** ✅ `Bearer <token>`

#### PUT `/api/todo/:id`

**Description**: Update existing todo by ID.

* **Body:** Same as POST
* **Response:** Updated todo object
* **Auth:** ✅ `Bearer <token>`

#### DELETE `/api/todo/:id`

**Description**: Soft-delete a todo by ID (updates `deleted_at` field).

* **Response:**

```json
{ "message": "success" }
```

* **Auth:** ✅ `Bearer <token>`

#### PATCH `/api/todo/:id/update-status`

**Description**: Toggle or set todo completion status.

* **Body:**

```json
{ "is_completed": true }
```

* **Response:**

```json
{ "is_completed": true }
```

* **Auth:** ✅ `Bearer <token>`


👉 API Docs URL: http://localhost:8080/docs/api

---

## 🧠 CLI Commands

| Command              | Description                |
|----------------------|----------------------------|
| generate\:controller | Generate controller        |
| generate\:command    | Generate a new CLI command |
| generate\:middleware | Generate a new middleware  |
| generate\:request    | Generate a new request     |
| generate\:event      | Create a new event         |
| generate\:listener   | Create a new listener      |
| generate\:job        | Create a new job           |

---

## 🐳 Docker Setup (default)

Beauty is designed to run **natively inside Docker**. By default, all services are containerized:

| Service | Image               | Notes                          |
|---------|---------------------|--------------------------------|
| app     | php:8.4-alpine + RR | RoadRunner + CLI build targets |
| db      | postgres:16         | PostgreSQL 16                  |
| redis   | redis\:alpine       | Redis 7                        |

---

## 🛠 Makefile Commands

| Category | Command                                | Description                                     |
|----------|----------------------------------------|-------------------------------------------------|
| Start    | `make up`                              | Start the DEV environment                       |
|          | `make prod`                            | Start the PROD environment                      |
| Stop     | `make stop`                            | Stop all containers                             |
|          | `make down`                            | Remove all containers and volumes               |
|          | `make restart`                         | Restart all containers                          |
|          | `make restart-container CONTAINER=...` | Restart a specific container                    |
|          | `make stop-container CONTAINER=...`    | Stop a specific container                       |
| PHP      | `make php <cmd>`                       | Run php command inside the app container        |
|          | `make beauty <cmd>`                    | Run beauty CLI command inside the app container |
| Tests    | `make test`                            | Run PHPUnit tests                               |
| Composer | `make composer <cmd>`                  | Run composer command inside the app container   |
| Shell    | `make bash`                            | Open bash shell inside the app container        |
| Logs     | `make logs <container>`                | View logs of specific container                 |
| Database | `make psql`                            | Access PostgreSQL CLI                           |
| Cache    | `make redis`                           | Access Redis CLI                                |

---

## 🔗 LICENSE

MIT