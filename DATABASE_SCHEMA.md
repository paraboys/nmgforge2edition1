# Database Schema Blueprint

## Implementation Order & Dependencies
1. `Organizations` (No dependencies)
2. `Users` (Depends on `Organizations`)
3. `Sla_Policies` (Depends on `Organizations`)
4. `Tickets` (Depends on `Organizations`, `Users`)
5. `Conversations` (Depends on `Organizations`, `Tickets`, `Users`)
6. `Tags` (Depends on `Organizations`)
7. `Tag_Ticket` (Depends on `Tickets`, `Tags`)
8. `Activity_Logs` (Depends on `Organizations`, `Tickets`, `Users`)

---

## Tables and Columns

### 1. `organizations`
- `id` (bigint, PK)
- `name` (varchar(255), not null)
- `created_at`, `updated_at` (timestamp)

### 2. `users`
- `id` (bigint, PK)
- `organization_id` (bigint, FK to organizations.id, ON DELETE CASCADE)
- `name` (varchar(255), not null)
- `email` (varchar(255), not null, unique per organization_id)
- `password` (varchar(255), not null)
- `role` (enum: 'admin', 'agent', 'customer', default: 'customer')
- `remember_token` (varchar(100), nullable)
- `created_at`, `updated_at` (timestamp)
**Indexes:** `(email, organization_id)`, `role`, `organization_id`

### 3. `sla_policies`
- `id` (bigint, PK)
- `organization_id` (bigint, FK to organizations.id, ON DELETE CASCADE)
- `priority` (enum: 'low', 'medium', 'high', 'urgent', not null)
- `response_time_minutes` (int, not null)
- `resolution_time_minutes` (int, not null)
- `created_at`, `updated_at` (timestamp)
**Indexes:** `(organization_id, priority)` (Unique)

### 4. `tickets`
- `id` (bigint, PK)
- `organization_id` (bigint, FK to organizations.id, ON DELETE CASCADE)
- `requester_id` (bigint, FK to users.id, ON DELETE CASCADE)
- `assignee_id` (bigint, nullable, FK to users.id, ON DELETE SET NULL)
- `subject` (varchar(255), not null)
- `description` (text, not null)
- `status` (enum: 'open', 'pending', 'resolved', 'closed', default: 'open')
- `priority` (enum: 'low', 'medium', 'high', 'urgent', default: 'medium')
- `created_at`, `updated_at` (timestamp)
**Indexes:** `organization_id`, `requester_id`, `assignee_id`, `status`, `priority`

### 5. `conversations`
- `id` (bigint, PK)
- `organization_id` (bigint, FK to organizations.id, ON DELETE CASCADE)
- `ticket_id` (bigint, FK to tickets.id, ON DELETE CASCADE)
- `user_id` (bigint, FK to users.id, ON DELETE CASCADE)
- `body` (text, not null)
- `is_internal` (boolean, default: false)
- `created_at`, `updated_at` (timestamp)
**Indexes:** `ticket_id`, `organization_id`

### 6. `tags`
- `id` (bigint, PK)
- `organization_id` (bigint, FK to organizations.id, ON DELETE CASCADE)
- `name` (varchar(255), not null)
- `created_at`, `updated_at` (timestamp)
**Indexes:** `(name, organization_id)`

### 7. `tag_ticket` (Pivot)
- `ticket_id` (bigint, FK to tickets.id, ON DELETE CASCADE)
- `tag_id` (bigint, FK to tags.id, ON DELETE CASCADE)
**Primary Key:** `(ticket_id, tag_id)`

### 8. `activity_logs`
- `id` (bigint, PK)
- `organization_id` (bigint, FK to organizations.id, ON DELETE CASCADE)
- `ticket_id` (bigint, FK to tickets.id, ON DELETE CASCADE)
- `user_id` (bigint, nullable, FK to users.id, ON DELETE SET NULL)
- `action` (varchar(255), not null)
- `details` (json, nullable)
- `created_at`, `updated_at` (timestamp)
**Indexes:** `ticket_id`, `organization_id`
