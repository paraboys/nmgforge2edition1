# PulseDesk — Database Schema

> Stack: Laravel 11 · MySQL 8 · Eloquent ORM

---

## Tables (creation order)

### 1. `organizations` (tenant root)

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, auto_increment | |
| name | VARCHAR(255) | NOT NULL | Company name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-safe identifier |
| created_at | TIMESTAMP | nullable | |
| updated_at | TIMESTAMP | nullable | |

**Indexes:** `PRIMARY` (id), `UNIQUE` (slug)

---

### 2. `users`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, auto_increment | |
| organization_id | BIGINT UNSIGNED | FK → organizations.id, NOT NULL, ON DELETE CASCADE | Tenant scope |
| name | VARCHAR(255) | NOT NULL | |
| email | VARCHAR(255) | NOT NULL | Uniqueness enforced at app level by org scope |
| password | VARCHAR(255) | NOT NULL | Hashed |
| role | ENUM('admin','agent','customer') | NOT NULL, DEFAULT 'customer' | |
| remember_token | VARCHAR(100) | nullable | |
| created_at | TIMESTAMP | nullable | |
| updated_at | TIMESTAMP | nullable | |

**Indexes:** `PRIMARY` (id), `INDEX` (organization_id, role), `INDEX` (email)

**Relationships:**
- `belongsTo` Organization
- `hasMany` Ticket (requester)
- `hasMany` Ticket (assignee)
- `hasMany` Comment
- `hasMany` ActivityLog

---

### 3. `tickets`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, auto_increment | |
| organization_id | BIGINT UNSIGNED | FK + index, NOT NULL, ON DELETE CASCADE | Tenant scope |
| requester_id | BIGINT UNSIGNED | FK → users.id, NOT NULL, ON DELETE CASCADE | The customer who created it |
| assignee_id | BIGINT UNSIGNED | FK → users.id, nullable, ON DELETE SET NULL | Agent assigned |
| subject | VARCHAR(255) | NOT NULL | |
| description | TEXT | NOT NULL | |
| status | ENUM('open','pending','resolved','closed') | NOT NULL, DEFAULT 'open' | |
| priority | ENUM('low','medium','high','urgent') | NOT NULL, DEFAULT 'medium' | |
| tags | JSON | nullable | Array of label strings |
| created_at | TIMESTAMP | nullable | |
| updated_at | TIMESTAMP | nullable | |

**Indexes:** `PRIMARY` (id), `INDEX` (organization_id, status, priority, assignee_id), `INDEX` (requester_id), `INDEX` (created_at)

**Relationships:**
- `belongsTo` Organization
- `belongsTo` User (requester)
- `belongsTo` User (assignee, nullable)
- `hasMany` Comment
- `hasMany` ActivityLog

---

### 4. `comments` (ticket conversations + internal notes)

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, auto_increment | |
| ticket_id | BIGINT UNSIGNED | FK + index, NOT NULL, ON DELETE CASCADE | |
| author_id | BIGINT UNSIGNED | FK → users.id, NOT NULL, ON DELETE CASCADE | |
| body | TEXT | NOT NULL | |
| is_internal | BOOLEAN | NOT NULL, DEFAULT false | true = internal note (agents only) |
| created_at | TIMESTAMP | nullable | |
| updated_at | TIMESTAMP | nullable | |

**Indexes:** `PRIMARY` (id), `INDEX` (ticket_id, created_at), `INDEX` (author_id)

**Relationships:**
- `belongsTo` Ticket
- `belongsTo` User (author)

---

### 5. `sla_policies` (Should-tier — depth feature)

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, auto_increment | |
| organization_id | BIGINT UNSIGNED | FK + index, NOT NULL, ON DELETE CASCADE | |
| priority | ENUM('low','medium','high','urgent') | NOT NULL | |
| response_minutes | INT UNSIGNED | NOT NULL | Target first-response time |
| resolution_minutes | INT UNSIGNED | NOT NULL | Target resolution time |
| created_at | TIMESTAMP | nullable | |
| updated_at | TIMESTAMP | nullable | |

**Indexes:** `PRIMARY` (id), `UNIQUE` (organization_id, priority)

**Relationships:** `belongsTo` Organization

---

### 6. `activity_logs` (Should-tier — audit trail)

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, auto_increment | |
| ticket_id | BIGINT UNSIGNED | FK + index, NOT NULL, ON DELETE CASCADE | |
| actor_id | BIGINT UNSIGNED | FK → users.id, nullable, ON DELETE SET NULL | nullable = system action |
| action | ENUM('created','assigned','status_changed','priority_changed','replied','internal_note','resolved','closed') | NOT NULL | |
| meta | JSON | nullable | e.g. {old_status: 'open', new_status: 'pending'} |
| created_at | TIMESTAMP | nullable | |

**Indexes:** `PRIMARY` (id), `INDEX` (ticket_id, created_at)

**Relationships:**
- `belongsTo` Ticket
- `belongsTo` User (actor, nullable)

---

### 7. `personal_access_tokens` (Laravel Sanctum)

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, auto_increment | |
| tokenable_type | VARCHAR(255) | NOT NULL | |
| tokenable_id | BIGINT UNSIGNED | NOT NULL | |
| name | VARCHAR(255) | NOT NULL | |
| token | VARCHAR(64) | UNIQUE, NOT NULL | |
| abilities | TEXT | nullable | |
| last_used_at | TIMESTAMP | nullable | |
| expires_at | TIMESTAMP | nullable | |
| created_at | TIMESTAMP | nullable | |
| updated_at | TIMESTAMP | nullable | |

**Indexes:** `PRIMARY` (id), `INDEX` (tokenable_type, tokenable_id), `UNIQUE` (token)

---

## Eloquent Model Relationships

```
Organization
  └── hasMany(User)
  └── hasMany(Ticket)
  └── hasMany(SlaPolicy)
  └── hasMany(ActivityLog)

User
  └── belongsTo(Organization)
  └── hasMany(Ticket, 'requester_id')
  └── hasMany(Ticket, 'assignee_id')
  └── hasMany(Comment, 'author_id')
  └── hasMany(ActivityLog, 'actor_id')

Ticket
  └── belongsTo(Organization)
  └── belongsTo(User, 'requester_id')
  └── belongsTo(User, 'assignee_id')
  └── hasMany(Comment)
  └── hasMany(ActivityLog)

Comment
  └── belongsTo(Ticket)
  └── belongsTo(User, 'author_id')

SlaPolicy
  └── belongsTo(Organization)

ActivityLog
  └── belongsTo(Ticket)
  └── belongsTo(User, 'actor_id')
```

---

## Tenant Isolation Enforcement

- **Global Scope `TenantScope`**: auto-applies `where('organization_id', auth()->user()->organization_id)` on `Ticket`, `Comment`, `User`, `SlaPolicy`, `ActivityLog` models.
- **Trait `BelongsToTenant`**: adds the scope, defines `organization()` relationship, boot method.
- **Middleware `SetTenantContext`**: runs after `auth:sanctum`, reads `auth()->user()->organization_id`, stores in `TenantContext` singleton.
- **Middleware `EnsureTenantScope`**: aborts 403 if request attempts to access a resource whose `organization_id` does not match the authenticated user's org.
- **Policies**: `TicketPolicy.view()` checks `ticket->organization_id === user->organization_id`; `viewAny` relies on the global scope.
- **No client-supplied tenant ID**: the tenant is ALWAYS derived from the authenticated user.
