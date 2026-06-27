# API Reference & Specification

## Implementation Order & Dependencies
1. **Auth Endpoints:** Required first for all subsequent API requests.
2. **User Endpoints:** Required for frontend context state and assigning tickets (Admin/Agent).
3. **Admin Settings (Tags, SLAs):** Foundation for tickets.
4. **Ticket Endpoints (CRUD):** The core entity.
5. **Conversation Endpoints:** Depends on Tickets.
6. **Dashboard/Metrics:** Depends on Tickets.

---

## 1. Auth (`AuthController`)

### `POST /api/login`
- **Auth:** None
- **Request:** `{"email": "...", "password": "..."}`
- **Validation:** `email` (required, email), `password` (required).
- **Response (200):** `{"token": "...", "user": {...}}`
- **Error (401):** `{"message": "Invalid credentials"}`

### `POST /api/logout`
- **Auth:** Sanctum Token
- **Response (200):** `{"message": "Logged out"}`

## 2. Users (`UserController`)
### `GET /api/users`
- **Auth:** Sanctum Token (Admin/Agent)
- **Query Params:** `role` (optional filter).
- **Response (200):** JSON array of users (id, name, email, role). Required to populate "Assignee" dropdowns.

## 3. Tickets (`TicketController`)

### `GET /api/tickets`
- **Auth:** Sanctum Token
- **Query Params:** `status`, `priority`, `assignee_id`, `search`.
- **Response (200):** Paginated JSON Resource of tickets.
- **Authorization:** Customer sees only `requester_id === user_id`.

### `POST /api/tickets`
- **Auth:** Sanctum Token
- **Request:** `{"subject": "...", "description": "...", "priority": "..."}`
- **Validation:** `subject` (required, max:255), `description` (required), `priority` (optional, in:low,medium,high,urgent).
- **Response (201):** Ticket Resource.

### `GET /api/tickets/{ticket}`
- **Auth:** Sanctum Token
- **Response (200):** Ticket Resource including relations (conversations, tags).
- **Error (404/403):** If not found or not authorized.

### `PUT /api/tickets/{ticket}`
- **Auth:** Sanctum Token (Admin/Agent only for status/assignee).
- **Request:** `{"status": "...", "assignee_id": 1, "priority": "..."}`
- **Validation:** `status` (in:open,pending,resolved,closed), `assignee_id` (nullable, exists:users,id), `priority` (in:low,medium,high,urgent).
- **Response (200):** Updated Ticket Resource.

## 4. Conversations (`ConversationController`)

### `GET /api/tickets/{ticket}/conversations`
- **Auth:** Sanctum Token
- **Response (200):** List of conversations. Customers do not receive `is_internal = true` items.

### `POST /api/tickets/{ticket}/conversations`
- **Auth:** Sanctum Token
- **Request:** `{"body": "...", "is_internal": false}`
- **Validation:** `body` (required), `is_internal` (boolean).
- **Response (201):** Conversation Resource.

## 5. Admin Settings

### `GET /api/tags` | `POST /api/tags`
- **Auth:** Sanctum Token (Admin/Agent)
- **Validation (POST):** `name` (required, string, max:255).
- **Response:** Tag Resource.

### `GET /api/sla-policies` | `PUT /api/sla-policies/{policy}`
- **Auth:** Sanctum Token (Admin only)
- **Validation (PUT):** `response_time_minutes` (int), `resolution_time_minutes` (int).
- **Response:** SLA Policy Resource.

## 6. Dashboard (`DashboardController`)

### `GET /api/dashboard/metrics`
- **Auth:** Sanctum Token (Admin/Agent)
- **Response (200):** `{"open_tickets": 10, "unassigned": 3, "resolved_today": 5}`
