# PulseDesk — API Reference

> Base URL: `http://127.0.0.1:8000/api`
> Auth: Laravel Sanctum (Bearer token header: `Authorization: Bearer <token>`)
> Content-Type: `application/json`
> All timestamps in ISO 8601 (UTC)

---

## Auth Endpoints (public)

### POST /register
Register a new organization and admin user.

**Request body:**
```json
{
  "organization_name": "Acme Corp",
  "name": "John Doe",
  "email": "admin@acme.test",
  "password": "password",
  "password_confirmation": "password"
}
```

**Validation rules:**
- `organization_name`: required, string, max 255
- `name`: required, string, max 255
- `email`: required, email, unique across users table
- `password`: required, string, min 8, confirmed

**Response 201:**
```json
{
  "data": {
    "user": { "id": 1, "name": "John Doe", "email": "admin@acme.test", "role": "admin" },
    "organization": { "id": 1, "name": "Acme Corp", "slug": "acme-corp" },
    "token": "1|abc123..."
  }
}
```

**Errors 422:**
```json
{ "message": "The given data was invalid.", "errors": { "email": ["The email has already been taken."] } }
```

---

### POST /login
Authenticate and receive token.

**Request body:**
```json
{ "email": "admin@acme.test", "password": "password" }
```

**Validation rules:**
- `email`: required, email
- `password`: required, string

**Response 200:**
```json
{
  "data": {
    "user": { "id": 1, "name": "John Doe", "email": "admin@acme.test", "role": "admin", "organization_id": 1 },
    "token": "1|abc123..."
  }
}
```

**Errors 401:**
```json
{ "message": "Invalid credentials." }
```

---

### POST /logout
Revoke current token. Requires auth.

**Headers:** `Authorization: Bearer <token>`

**Response 200:**
```json
{ "message": "Token revoked successfully." }
```

---

### GET /me
Get current authenticated user.

**Headers:** `Authorization: Bearer <token>`

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "admin@acme.test",
    "role": "admin",
    "organization": { "id": 1, "name": "Acme Corp", "slug": "acme-corp" }
  }
}
```

---

## Ticket Endpoints (auth required)

### GET /tickets
List tickets. Tenant-scoped automatically. Supports filtering, sorting, pagination.

**Query parameters:**
- `status` (optional): `open` | `pending` | `resolved` | `closed`
- `priority` (optional): `low` | `medium` | `high` | `urgent`
- `assignee` (optional): user ID or `null` for unassigned
- `requester` (optional): user ID (admin/agent only; customers ignore this)
- `q` (optional): search string (searches subject + description) — Sprint 4
- `sort` (optional): `created_at` | `updated_at` | `priority` (default: `created_at`)
- `direction` (optional): `asc` | `desc` (default: `desc`)
- `page` (optional): integer (default: 1)
- `per_page` (optional): integer, max 100 (default: 20)

**Authorization:**
- Admin/Agent: sees all tickets in their org
- Customer: sees only their own tickets (requester_id = their id); `requester` param is ignored

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "subject": "Cannot login",
      "description": "I get a 500 error when...",
      "status": "open",
      "priority": "high",
      "tags": ["login", "bug"],
      "requester": { "id": 5, "name": "Jane Customer" },
      "assignee": null,
      "created_at": "2024-06-27T10:30:00Z",
      "updated_at": "2024-06-27T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

---

### POST /tickets
Create a new ticket.

**Request body:**
```json
{
  "subject": "Cannot login",
  "description": "I get a 500 error when trying to login.",
  "priority": "high",
  "tags": ["login", "bug"]
}
```

**Validation rules:**
- `subject`: required, string, max 255
- `description`: required, string
- `priority`: required, in: `low,medium,high,urgent`
- `tags`: optional, array of strings, max 10 items, each max 50 chars

**Authorization:**
- Any authenticated user in the org.

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "subject": "Cannot login",
    "description": "I get a 500 error when trying to login.",
    "status": "open",
    "priority": "high",
    "tags": ["login", "bug"],
    "requester": { "id": 5, "name": "Jane Customer" },
    "assignee": null,
    "created_at": "2024-06-27T10:30:00Z",
    "updated_at": "2024-06-27T10:30:00Z"
  }
}
```

---

### GET /tickets/{id}
Get a single ticket with its comments and activity log.

**Authorization:**
- Admin/Agent: any ticket in their org
- Customer: only their own tickets

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "subject": "Cannot login",
    "description": "I get a 500 error when trying to login.",
    "status": "open",
    "priority": "high",
    "tags": ["login", "bug"],
    "requester": { "id": 5, "name": "Jane Customer" },
    "assignee": null,
    "comments": [
      {
        "id": 1,
        "body": "Thanks for reporting. We'll look into this.",
        "is_internal": false,
        "author": { "id": 2, "name": "Agent Alice", "role": "agent" },
        "created_at": "2024-06-27T11:00:00Z"
      }
    ],
    "activity_logs": [
      {
        "id": 1,
        "action": "created",
        "actor": { "id": 5, "name": "Jane Customer" },
        "meta": null,
        "created_at": "2024-06-27T10:30:00Z"
      }
    ],
    "created_at": "2024-06-27T10:30:00Z",
    "updated_at": "2024-06-27T10:30:00Z"
  }
}
```

**Errors 404:** Ticket not found or not in user's org.

---

### PUT /tickets/{id}
Update a ticket. Admin/Agent can update all fields; Customer can only update subject/description on their own tickets.

**Request body:**
```json
{
  "subject": "Cannot login — updated",
  "description": "More details: it happens on mobile.",
  "status": "pending",
  "priority": "urgent",
  "assignee_id": 2,
  "tags": ["login", "bug", "mobile"]
}
```

**Validation rules:**
- `subject`: optional, string, max 255
- `description`: optional, string
- `status`: optional, in: `open,pending,resolved,closed`
- `priority`: optional, in: `low,medium,high,urgent`
- `assignee_id`: optional, nullable, exists in users table, same org
- `tags`: optional, array of strings, max 10 items, each max 50 chars

**Authorization:**
- Admin/Agent: any ticket in org
- Customer: only own tickets; `status`, `priority`, `assignee_id` are ignored

**Response 200:** Updated ticket resource (same format as GET).

**Errors 403:** Customer trying to change status/priority/assignee.

---

### DELETE /tickets/{id}
Delete a ticket. Admin only.

**Authorization:**
- Admin only.

**Response 204:** No content.

**Errors 403:** Non-admin attempting delete.

---

## Comment Endpoints (auth required)

### GET /tickets/{ticket_id}/comments
List comments on a ticket.

**Authorization:**
- Admin/Agent: sees all comments (including internal notes)
- Customer: sees only `is_internal = false` comments

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "body": "Thanks for reporting. We'll look into this.",
      "is_internal": false,
      "author": { "id": 2, "name": "Agent Alice", "role": "agent" },
      "created_at": "2024-06-27T11:00:00Z"
    }
  ]
}
```

---

### POST /tickets/{ticket_id}/comments
Add a comment or internal note to a ticket.

**Request body:**
```json
{
  "body": "Checking the server logs now.",
  "is_internal": true
}
```

**Validation rules:**
- `body`: required, string
- `is_internal`: optional, boolean (default false)

**Authorization:**
- Admin/Agent: can set `is_internal` to true
- Customer: `is_internal` is forced to false; can only comment on their own tickets

**Response 201:**
```json
{
  "data": {
    "id": 2,
    "body": "Checking the server logs now.",
    "is_internal": true,
    "author": { "id": 2, "name": "Agent Alice", "role": "agent" },
    "created_at": "2024-06-27T11:15:00Z"
  }
}
```

---

## User Endpoints (auth required)

### GET /users
List users in the organization. Admin/Agent only. Customers get 403.

**Query parameters:**
- `role` (optional): `admin` | `agent` | `customer`
- `page`, `per_page` (optional)

**Response 200:** Array of UserResource with pagination meta.

---

### POST /users
Create a new user (agent or customer) in the organization. Admin only.

**Request body:**
```json
{
  "name": "New Agent",
  "email": "agent@acme.test",
  "password": "password",
  "role": "agent"
}
```

**Validation rules:**
- `name`: required, string, max 255
- `email`: required, email, unique
- `password`: required, string, min 8
- `role`: required, in: `agent,customer` (admin cannot create other admins via API)

**Response 201:** UserResource.

---

### GET /users/{id}
Get a single user. Admin/Agent: any user in org. Customer: only themselves.

**Response 200:** UserResource.

---

## Error Response Format

All errors follow this envelope:

```json
{
  "message": "Human-readable error summary",
  "errors": {
    "field_name": ["Error detail 1", "Error detail 2"]
  }
}
```

**Standard HTTP status codes:**
- 200 OK — Success
- 201 Created — Resource created
- 204 No Content — Deleted
- 401 Unauthorized — No valid token
- 403 Forbidden — Authenticated but not allowed (role/tenant mismatch)
- 404 Not Found — Resource does not exist or not in tenant scope
- 422 Unprocessable Entity — Validation failure
- 500 Internal Server Error — Unexpected server error
