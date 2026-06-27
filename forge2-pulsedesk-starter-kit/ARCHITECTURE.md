# PulseDesk Architecture

> PulseDesk — A multi-tenant SaaS Helpdesk built for the Forge 2 · Edition 1 hackathon.
> Stack: Laravel 11, PHP 8.2+, MySQL 8, React 19, Vite, Tailwind CSS, Laravel Sanctum, Pest/PHPUnit.

---

## 1. Multi-tenancy design

### Principle: tenant isolation from the auth session
Every data row (except the `users` table which itself has `organization_id`) is scoped to the authenticated user's organization via a **global query scope** and **Laravel policies**.

### Tenant derivation
- The tenant is **never** supplied by the client.
- On every authenticated request, the `organization_id` is read from the `User` model (`$user->organization_id`).
- A `TenantScope` global Eloquent scope applies `where('organization_id', $user->organization_id)` to all models that implement the `BelongsToTenant` trait.
- A `tenant` middleware runs after `auth:sanctum` and binds the current tenant to a `TenantContext` singleton for the request lifecycle.

### Policy enforcement
- All controllers use `authorizeResource()` or explicit `authorize()` calls.
- `TicketPolicy` checks: `view` → `ticket->organization_id === user->organization_id`.
- `viewAny` applies the tenant scope automatically.
- Admins can see all tickets in their org; agents see all in their org; customers see only their own tickets.

---

## 2. Data model (ER design)

```
Organization
  ├── id (PK)
  ├── name
  ├── slug
  ├── created_at / updated_at
  │
  └── has_many Users
  └── has_many Tickets
  └── has_many SlaPolicies
  └── has_many ActivityLogs

User
  ├── id (PK)
  ├── organization_id (FK → organizations.id) + index
  ├── name
  ├── email (unique within org, but scoped to org)
  ├── password
  ├── role (enum: admin | agent | customer)
  ├── created_at / updated_at
  │
  └── belongs_to Organization
  └── has_many Tickets (as requester)
  └── has_many Tickets (as assignee)
  └── has_many Comments
  └── has_many ActivityLogs (as actor)

Ticket
  ├── id (PK)
  ├── organization_id (FK + index) ← tenant scope
  ├── requester_id (FK → users.id) — the customer who created it
  ├── assignee_id (FK → users.id, nullable) — the agent assigned
  ├── subject
  ├── description
  ├── status (enum: open | pending | resolved | closed)
  ├── priority (enum: low | medium | high | urgent)
  ├── tags (JSON array of label strings)
  ├── created_at / updated_at
  │
  └── belongs_to Organization
  └── belongs_to User (requester)
  └── belongs_to User (assignee, nullable)
  └── has_many Comments
  └── has_many ActivityLogs

Comment
  ├── id (PK)
  ├── ticket_id (FK + index)
  ├── author_id (FK → users.id)
  ├── body (text)
  ├── is_internal (boolean, default false) — true = agents-only internal note
  ├── created_at / updated_at
  │
  └── belongs_to Ticket
  └── belongs_to User (author)

SlaPolicy (Should-tier — depth feature)
  ├── id (PK)
  ├── organization_id (FK + index)
  ├── priority (enum: low | medium | high | urgent)
  ├── response_minutes (int)
  ├── resolution_minutes (int)
  ├── created_at / updated_at
  │
  └── belongs_to Organization

ActivityLog (Should-tier — audit trail)
  ├── id (PK)
  ├── ticket_id (FK + index)
  ├── actor_id (FK → users.id, nullable for system)
  ├── action (enum: created | assigned | status_changed | priority_changed | replied | internal_note | resolved | closed)
  ├── meta (JSON — e.g. {old_status: 'open', new_status: 'pending'})
  ├── created_at
  │
  └── belongs_to Ticket
  └── belongs_to User (actor)
```

### Indexes summary
- `organizations`: `slug` unique
- `users`: `organization_id` + `role` composite index; `email` index (within org scope enforced by app)
- `tickets`: `organization_id` + `status` + `priority` + `assignee_id` composite index; `requester_id` index; `created_at` index
- `comments`: `ticket_id` + `created_at` composite index; `author_id` index
- `activity_logs`: `ticket_id` + `created_at` composite index
- `sla_policies`: `organization_id` + `priority` unique

---

## 3. Authentication flow

1. **Register** (`POST /api/register`) — creates an Organization + User with role `admin`. Only the first user of an org is admin. Additional users are created by admin invite or sign-up with org slug.
2. **Login** (`POST /api/login`) — validates credentials, issues Laravel Sanctum token (`token` or `cookie` for SPA).
3. **Me** (`GET /api/me`) — returns current user + organization.
4. **Logout** (`POST /api/logout`) — revokes token.
5. **Sanctum config**: `sanctum` guard, stateful domains for SPA (`127.0.0.1:5173`).
6. **Middleware**: `auth:sanctum` on all API routes except `register`/`login`.

---

## 4. Authorization strategy

| Role | Organization scope | Ticket visibility | Ticket actions | User management |
|---|---|---|---|---|
| **Admin** | Own org only | All tickets in org | Full CRUD, assign, change status/priority, view internal notes | Can create agents/customers |
| **Agent** | Own org only | All tickets in org | Full CRUD, assign, change status/priority, view internal notes | Can view customers only |
| **Customer** | Own org only | Own tickets only | Create, view, reply (public) | Cannot modify status/priority, cannot view internal notes |

- Every controller checks `auth()->user()->role` and `organization_id` before any DB operation.
- No cross-tenant leakage is possible because of the global `TenantScope` + policy checks.

---

## 5. API architecture

- RESTful JSON API, all endpoints under `/api/*`.
- Standard HTTP verbs + status codes.
- Pagination: `?page=1&per_page=20` default.
- Filtering: `?status=open&priority=high&assignee=3` on tickets index.
- Search: `?q=search+text` on subject + description (Sprint 4).
- Sorting: `?sort=created_at&direction=desc` default.
- Response envelope: `{'data': {...}, 'meta': {...}}` for collections.
- Error format: `{'message': '...', 'errors': {...}}` for validation.

---

## 6. Frontend architecture

- **React 19** + **Vite** + **Tailwind CSS**.
- SPA with `react-router-dom` v7.
- Context-based auth state (`AuthContext`) storing user + token + org.
- API layer: `axios` instance with base URL, interceptors for auth header + 401 redirect.
- Page structure:
  - `LoginPage` — public
  - `RegisterPage` — public
  - `DashboardPage` — protected, ticket list + filters
  - `TicketDetailPage` — protected, conversation + internal notes
  - `NewTicketPage` — protected (customers create; agents create on behalf)
  - `AdminSettingsPage` — admin-only, org management, user invites (Should-tier)
- Reusable components: `TicketCard`, `TicketList`, `CommentThread`, `FilterBar`, `StatusBadge`, `PriorityBadge`, `UserAvatar`, `AuthGuard`, `TenantGuard`.
- No server-side rendering for app UI; Blade may be used for auth scaffolding only.

---

## 7. Folder structure

```
forge2-parassingh/
├── backend/                    # Laravel 11
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Organization.php
│   │   │   ├── User.php
│   │   │   ├── Ticket.php
│   │   │   ├── Comment.php
│   │   │   ├── SlaPolicy.php
│   │   │   └── ActivityLog.php
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── TicketController.php
│   │   │   │   ├── CommentController.php
│   │   │   │   └── UserController.php
│   │   │   ├── Requests/          # FormRequest validation
│   │   │   ├── Resources/         # API resource transformers
│   │   │   └── Middleware/
│   │   │       └── TenantScope.php
│   │   ├── Policies/
│   │   │   ├── TicketPolicy.php
│   │   │   └── UserPolicy.php
│   │   ├── Scopes/
│   │   │   └── TenantScope.php
│   │   └── Traits/
│   │       └── BelongsToTenant.php
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   │       ├── DatabaseSeeder.php
│   │       └── DemoSeeder.php
│   ├── routes/
│   │   └── api.php
│   ├── tests/
│   │   ├── Feature/
│   │   └── Unit/
│   └── .env.example
├── frontend/                   # React 19 + Vite
│   ├── src/
│   │   ├── main.jsx
│   │   ├── App.jsx
│   │   ├── context/
│   │   │   └── AuthContext.jsx
│   │   ├── pages/
│   │   │   ├── LoginPage.jsx
│   │   │   ├── RegisterPage.jsx
│   │   │   ├── DashboardPage.jsx
│   │   │   ├── TicketDetailPage.jsx
│   │   │   └── NewTicketPage.jsx
│   │   ├── components/
│   │   │   ├── TicketCard.jsx
│   │   │   ├── TicketList.jsx
│   │   │   ├── CommentThread.jsx
│   │   │   ├── FilterBar.jsx
│   │   │   ├── StatusBadge.jsx
│   │   │   ├── PriorityBadge.jsx
│   │   │   ├── UserAvatar.jsx
│   │   │   ├── AuthGuard.jsx
│   │   │   └── Layout.jsx
│   │   ├── api/
│   │   │   └── api.js
│   │   └── utils/
│   │       └── helpers.js
│   ├── index.html
│   └── vite.config.js
├── .github/workflows/ci.yml
├── agents/
│   ├── hermes/hermes-config.yaml
│   └── openclaw/openclaw.json
├── sprints/
│   ├── sprint-01.md
│   ├── sprint-02.md
│   ├── sprint-03.md
│   ├── sprint-04.md
│   └── sprint-05.md
├── evidence/
│   └── screenshots/
├── slack-export/
│   └── screenshots/
├── agent-log.md
├── ARCHITECTURE.md
├── DATABASE_SCHEMA.md
├── API_SPEC.md
├── UI_FLOW.md
├── SPRINT_BACKLOG.md
├── SUBMISSION.md
└── README.md
```

---

## 8. CI/CD strategy

- **GitHub Actions**: `.github/workflows/ci.yml`
  - Runs on every PR and push to `main`.
  - Backend: `composer install`, `migrate`, `php artisan test` (Pest/PHPUnit).
  - Frontend: `npm install`, `npm run build` (compile check).
  - MySQL 8 service container for integration tests.
- **No auto-merge**: humans review and merge PRs.
- **No deployment pipeline**: runs locally from a fresh clone.

---

## 9. Testing strategy

- **Backend**: Pest or PHPUnit.
  - Feature tests for every API endpoint.
  - Tenant isolation tests: `test_cross_org_user_cannot_read_other_org_tickets`.
  - Auth tests: register, login, logout, token expiry.
  - Role tests: customer cannot update status, admin can assign tickets.
  - Model tests: relationships, scopes, policies.
- **Frontend**: No E2E tests required (time-bound); smoke test for build + unit tests for helpers if time permits.
- **Minimum test coverage**: all `Must` tier API endpoints must have at least one passing feature test.

---

## 10. Key decisions log

1. **Tenant scope via global Eloquent scope + middleware** — ensures no manual query scoping is forgotten; prevents cross-tenant leakage by design.
2. **Organization created at registration** — first user is admin; org slug derived from name or supplied; users always belong to exactly one org.
3. **Tags stored as JSON array** — simple, no separate tags table needed for MVP; may be normalized later.
4. **Comments table serves both public replies and internal notes** — `is_internal` flag controls visibility; keeps schema simple.
5. **SLA policies and Activity logs are Should-tier** — schema designed but not enforced by Sprint 1; tables created early but features ship later.
6. **No separate customer portal** — React SPA serves all roles; role-based UI controls render/hide features.
7. **No real-time (WebSocket) for MVP** — polling or manual refresh acceptable; stretch goal.
8. **Attachments deferred to Should/Stretch** — architecture has placeholder but no implementation in Must tier.
