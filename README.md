
# User CRUD with Profile — Laravel + Eloquent

Tech documentation for a **CRUD system** that manages **Users** and their **Profile** using **Laravel + Eloquent ORM**.

This project satisfies the practical requirements:

- **Entities:** User + Profile  
- **Relationship:** **1:1 (User → Profile)**  
- **CRUD:** Full CRUD for **User**  
- **Create flow:** When creating a user, allow **creating the profile together**  
- **Email uniqueness:** Do **not** allow duplicate emails  
- **Listing:** List users including **profile data** using Eloquent relationship  

---

## 1. Tech Stack

- **Language:** PHP `X.Y.Z`
- **Framework:** Laravel `X.Y.Z`
- **ORM:** Eloquent (built-in)
- **Database:** MySQL / PostgreSQL / SQLite

> Replace the versions after installation using:

```bash
php -v
php artisan --version
composer --version


```

## 2. Project Setup

### 2.1 Clone repository

```bash
git clone https://github.com/LucasLydio/eloquent-crud.git
cd eloquent-crud
```

### 2.2 Install dependencies

```bash
composer install
```

### 2.3 Environment configuration

Copy `.env.example` to `.env` and generate the app key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database connection in `.env`.

**Example (MySQL):**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crud_users
DB_USERNAME=root
DB_PASSWORD=
```

### 2.4 Run migrations

```bash
php artisan migrate
```

### 2.5 Run the API

```bash
php artisan serve
```

Application will be available at:

* `http://127.0.0.1:8000`

---

## 3. Project Structure

Recommended structure for a clean and maintainable CRUD:

```txt
app/
  Models/
    User.php
    Profile.php

  Http/
    Controllers/
      UserController.php

    Requests/
      StoreUserRequest.php
      UpdateUserRequest.php

routes/
  api.php

database/
  migrations/
    0001_create_profiles_table.php
    0002_add_profile_id_to_users_table.php
```

### Responsibilities

* **Models**: Eloquent relationship + fillable fields
* **Requests**: validation rules for create/update (unique email, etc.)
* **Controller**: CRUD endpoints and relationship loading (`with('profile')`)
* **Migrations**: tables + constraints for 1:1 and unique email
* **Routes**: API endpoints under `/api`

---

## 4. Expected Database Modeling

### 4.1 Tables

#### `profiles`

| Column       | Type        | Constraints |
| ------------ | ----------- | ----------- |
| id           | bigint/uuid | PK          |
| profile_name | string      | NOT NULL    |
| created_at   | timestamp   |             |
| updated_at   | timestamp   |             |

#### `users`

| Column     | Type        | Constraints                              |
| ---------- | ----------- | ---------------------------------------- |
| id         | bigint/uuid | PK                                       |
| name       | string      | NOT NULL                                 |
| email      | string      | NOT NULL, **UNIQUE**                     |
| password   | string      | NOT NULL (hashed)                        |
| profile_id | bigint/uuid | NOT NULL, **UNIQUE**, FK → `profiles.id` |
| created_at | timestamp   |                                          |
| updated_at | timestamp   |                                          |

### 4.2 Constraints (Mandatory)

* `users.email` must be **unique** to prevent duplicates
* `users.profile_id` must be **unique** to enforce **1:1** relationship
  (one profile cannot be linked to multiple users)

---

## 5. Eloquent Relationship

Because the schema uses `profile_id` inside `users`, the recommended relationship mapping is:

* **User belongsTo Profile**
* **Profile hasOne User**

Expected behavior:

* When listing users, load profile using `with('profile')`
* When creating user, create profile first (if provided) and assign `profile_id`

---

## 6. API Routes

All routes below are under: `/api`

### 6.1 User CRUD

| Method | Route         | Description                                    |
| ------ | ------------- | ---------------------------------------------- |
| POST   | `/users`      | Create user (allows profile creation together) |
| GET    | `/users`      | List users (must include profile)              |
| GET    | `/users/{id}` | Get user details (include profile)             |
| PUT    | `/users/{id}` | Update user (optionally profile data)          |
| DELETE | `/users/{id}` | Delete user                                    |

> Note: Profile does not need separate CRUD endpoints to satisfy the requirements, but you may add them optionally.

---

## 7. Request/Response Examples

### 7.1 Create User with Profile (Required)

**POST** `/api/users`

Request:

```json
{
  "name": "John Doe",
  "email": "john@email.com",
  "password": "123456",
  "profile": {
    "profile_name": "admin"
  }
}
```

Expected response (example):

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@email.com",
  "profile": {
    "id": 1,
    "profile_name": "admin"
  }
}
```

Rules applied:

* `email` must be unique
* `password` must be hashed
* profile can be created in the same request

---

### 7.2 Create User without Profile (Optional)

If your implementation allows creating a user without profile, document it clearly.
If you want to strictly follow the schema `profile_id NOT NULL`, then profile should be required.

**POST** `/api/users`

Request:

```json
{
  "name": "Jane Doe",
  "email": "jane@email.com",
  "password": "123456",
  "profile": {
    "profile_name": "common"
  }
}
```

---

### 7.3 List Users with Profile (Required)

**GET** `/api/users`

Expected response:

```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "john@email.com",
    "profile": {
      "id": 1,
      "profile_name": "admin"
    }
  },
  {
    "id": 2,
    "name": "Jane Doe",
    "email": "jane@email.com",
    "profile": {
      "id": 2,
      "profile_name": "common"
    }
  }
]
```

Implementation note:

* Must use Eloquent relationship loading: `User::with('profile')->get()`

---

### 7.4 Get User by ID with Profile

**GET** `/api/users/{id}`

Example:
**GET** `/api/users/1`

Response:

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@email.com",
  "profile": {
    "id": 1,
    "profile_name": "admin"
  }
}
```

---

### 7.5 Update User (with optional profile update)

**PUT** `/api/users/{id}`

Request:

```json
{
  "name": "John Updated",
  "profile": {
    "profile_name": "manager"
  }
}
```

Response:

```json
{
  "id": 1,
  "name": "John Updated",
  "email": "john@email.com",
  "profile": {
    "id": 1,
    "profile_name": "manager"
  }
}
```

Rules applied:

* If updating `email`, ensure it remains unique (ignore current user id)
* If updating profile, validate `profile.profile_name`

---

### 7.6 Delete User

**DELETE** `/api/users/{id}`

Example:
**DELETE** `/api/users/1`

Response example:

```json
{
  "message": "User deleted successfully"
}
```

---

## 8. Validation Rules (Expected)

### Create (`POST /users`)

* `name`: required, string
* `email`: required, email, **unique:users,email**
* `password`: required, min length (e.g., 6)
* `profile.profile_name`: required, string

### Update (`PUT /users/{id}`)

* `name`: optional, string
* `email`: optional, email, unique ignoring current user
* `password`: optional, hash if provided
* `profile.profile_name`: optional, string

---

## 9. Quick Testing (cURL)

### Create user

```bash
curl -X POST http://127.0.0.1:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@email.com","password":"123456","profile":{"profile_name":"admin"}}'
```

### List users

```bash
curl http://127.0.0.1:8000/api/users
```

### Get user by id

```bash
curl http://127.0.0.1:8000/api/users/1
```

### Update user

```bash
curl -X PUT http://127.0.0.1:8000/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"John Updated","profile":{"profile_name":"manager"}}'
```

### Delete user

```bash
curl -X DELETE http://127.0.0.1:8000/api/users/1
```

---

## 10. Delivery Checklist

* [ ] README includes: setup steps, dependencies, language version, ORM version
* [ ] User CRUD implemented (create/list/get/update/delete)
* [ ] Create user with profile in same request
* [ ] Unique email enforced (validation + DB constraint)
* [ ] Listing returns profile data using Eloquent relationship

---

```
```
