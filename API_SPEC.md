# PulseDesk API Specification

All endpoints are prefixed with `/api`.

## General Conventions
- **Authentication:** Bearer Token (Sanctum) required for all endpoints except `/login` and `/register`.
- **Response Format:** JSON. Success responses use standard `data` wrapping (Laravel Resources).
- **Errors:** Standard HTTP status codes (401 Unauthorized, 403 Forbidden, 404 Not Found, 422 Unprocessable Entity).

---

## 1. Authentication

### `POST /login`
- **Auth Required:** No
- **Request Body:**
  ```json
  {
    "email": "agent@acme.test",
    "password": "password"
  }
  ```
- **Validation:** `email` (required, email), `password` (required).
- **Success Response (200 OK):**
  ```json
  {
    "token": "1|abc123def...",
    "user": {
      "id": 2,
      "name": "Agent Smith",
      "email": "agent@acme.test",
      "role": "agent",
      "organization_id": 1
    }
  }
  ```
- **Error Response (401 Unauthorized):**
  ```json
  { "message": "Invalid credentials." }
  ```

### `POST /logout`
- **Auth Required:** Yes
- **Success Response (200 OK):** `{ "message": "Logged out" }`

---

## 2. Users (Me)

### `GET /user`
- **Auth Required:** Yes
- **Success Response (200 OK):** Returns current authenticated user object.

---

## 3. Tickets

### `GET /tickets`
- **Auth Required:** Yes
- **Query Params (Optional):** `status` (string), `priority` (string), `assignee_id` (int), `search` (string for subject/body).
- **Authorization:** Admin/Agent see all in org. Customer sees only their own tickets.
- **Success Response (200 OK):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "subject": "Need help with login",
        "status": "open",
        "priority": "high",
        "requester": { "id": 3, "name": "Customer Joe" },
        "assignee": { "id": 2, "name": "Agent Smith" },
        "created_at": "2026-06-27T10:00:00Z"
      }
    ],
    "links": { ... },
    "meta": { "current_page": 1, "last_page": 5 }
  }
  ```

### `GET /tickets/{id}`
- **Auth Required:** Yes
- **Success Response (200 OK):** Returns full ticket details, including tags and conversations.
- **Error (404 Not Found):** If ticket does not exist or does not belong to the user's organization (or if customer and not requester).

### `POST /tickets`
- **Auth Required:** Yes
- **Authorization:** Customers can create for themselves. Agents/Admins can create on behalf of a customer (requires passing `requester_id`).
- **Request Body:**
  ```json
  {
    "subject": "Billing issue",
    "description": "I was charged twice.",
    "priority": "high"
  }
  ```
- **Validation:** `subject` (required, max:255), `description` (required). `priority` (optional, in:low,medium,high,urgent).
- **Success Response (201 Created):** Returns created ticket object.
- **Error (422):** Validation errors.

### `PUT /tickets/{id}`
- **Auth Required:** Yes
- **Authorization:** Admin/Agent can update any ticket in org. Customer can only update their own ticket (maybe only description if open, but typically only add comments). Let's restrict PUT to Admin/Agent.
- **Request Body (Partial updates allowed):**
  ```json
  {
    "status": "resolved",
    "priority": "medium",
    "assignee_id": 2
  }
  ```
- **Validation:** `status` (in:open,pending,resolved,closed), `priority` (in:low,medium,high,urgent), `assignee_id` (exists:users,id).
- **Success Response (200 OK):** Returns updated ticket object.

---

## 4. Conversations (Replies/Notes)

### `GET /tickets/{ticket_id}/conversations`
- **Auth Required:** Yes
- **Authorization:** Customer sees only `is_internal: false`. Admin/Agent see all.
- **Success Response (200 OK):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "body": "I will look into this.",
        "is_internal": true,
        "author": { "id": 2, "name": "Agent Smith" },
        "created_at": "..."
      }
    ]
  }
  ```

### `POST /tickets/{ticket_id}/conversations`
- **Auth Required:** Yes
- **Request Body:**
  ```json
  {
    "body": "Could you provide your invoice number?",
    "is_internal": false
  }
  ```
- **Validation:** `body` (required). `is_internal` (boolean, required). Customers cannot pass `is_internal: true`.
- **Success Response (201 Created):** Returns created conversation object.

---

## 5. Tags

### `GET /tags`
- **Auth Required:** Yes (Admin/Agent only).
- **Success Response (200 OK):** List of tags for the organization.

### `POST /tickets/{ticket_id}/tags`
- **Auth Required:** Yes (Admin/Agent only).
- **Request Body:**
  ```json
  { "tag_ids": [1, 3] }
  ```
- **Success Response (200 OK):** `{ "message": "Tags updated" }`

---

## 6. Activity Logs

### `GET /tickets/{ticket_id}/logs`
- **Auth Required:** Yes (Admin/Agent only).
- **Success Response (200 OK):** List of activity logs for the ticket.
