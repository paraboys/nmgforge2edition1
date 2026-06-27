# PulseDesk — MASTER IMPLEMENTATION CHECKLIST

> Lead Reviewer Final Audit. This document contains every task, fix, and gap identified across the frozen implementation specification. A coding agent must implement every item in this checklist to complete the project. Tasks are ordered by dependency and critical path.

---

## AUDIT SUMMARY: GAPS IDENTIFIED

| # | Category | Gap Found | Severity | Fix Location |
|---|----------|-----------|----------|--------------|
| 1 | API | `POST /api/register` missing from starter API_REFERENCE.md | **CRITICAL** | API_REFERENCE.md §2.1 |
| 2 | API | `GET /api/me` missing (only `GET /user` in ROUTING_PLAN) | **CRITICAL** | API_REFERENCE.md §2.3, ROUTING_PLAN |
| 3 | API | `POST /api/users` missing (only `GET /users`) | **CRITICAL** | API_REFERENCE.md §3.2 |
| 4 | API | `DELETE /api/tickets/{id}` missing | **CRITICAL** | API_REFERENCE.md §4.5 |
| 5 | API | `PATCH /api/tickets/{id}/assign` missing | **HIGH** | API_REFERENCE.md §4.6 |
| 6 | API | `DELETE /api/comments/{id}` missing | **HIGH** | API_REFERENCE.md §5.3 |
| 7 | API | `GET /api/dashboard` missing (only `/dashboard/metrics`) | **HIGH** | API_REFERENCE.md §6.1, ROUTING_PLAN |
| 8 | DB | `comments` has redundant `organization_id` | **MEDIUM** | DATABASE_SCHEMA.md §5 |
| 9 | DB | Missing `slug` on `organizations` | **HIGH** | DATABASE_SCHEMA.md §2 |
| 10 | Backend | No `app/Enums/` directory or files | **HIGH** | BACKEND_IMPLEMENTATION_GUIDE.md §2 |
| 11 | Backend | No `app/Services/` directory | **HIGH** | BACKEND_IMPLEMENTATION_GUIDE.md §6 |
| 12 | Backend | No `app/Traits/BelongsToTenant.php` | **CRITICAL** | BACKEND_IMPLEMENTATION_GUIDE.md §4 |
| 13 | Backend | No `app/Exceptions/` directory | **LOW** | COMPLETE_FILE_STRUCTURE.md |
| 14 | Backend | No `app/Console/Commands/DemoDataCommand.php` | **LOW** | COMPLETE_FILE_STRUCTURE.md |
| 15 | Backend | No `database/factories/` files | **MEDIUM** | COMPLETE_FILE_STRUCTURE.md |
| 16 | Backend | No `AuthServiceProvider` policy registration | **CRITICAL** | BACKEND_IMPLEMENTATION_GUIDE.md §15.3 |
| 17 | Backend | No `EnsureTenantContext` middleware alias in bootstrap/app.php | **CRITICAL** | BACKEND_IMPLEMENTATION_GUIDE.md §15.2 |
| 18 | Frontend | No `RegisterPage` in starter kit | **CRITICAL** | FRONTEND_IMPLEMENTATION_GUIDE.md §7.2 |
| 19 | Frontend | No `NewTicketPage` / `EditTicketPage` | **HIGH** | FRONTEND_IMPLEMENTATION_GUIDE.md §7.6, 7.7 |
| 20 | Frontend | No `ToastContext` / `Toast` component | **MEDIUM** | FRONTEND_IMPLEMENTATION_GUIDE.md §4.2, 8.1 |
| 21 | Frontend | No `AuthGuard` / `GuestGuard` / `RoleGuard` | **CRITICAL** | FRONTEND_IMPLEMENTATION_GUIDE.md §6.2 |
| 22 | Frontend | No `routes.jsx` file | **CRITICAL** | FRONTEND_IMPLEMENTATION_GUIDE.md §6.1 |
| 23 | Testing | No `TenantIsolationTest` file | **CRITICAL** | TESTING_PLAN.md §1.1 |
| 24 | Testing | No `AuthTest` file | **CRITICAL** | TESTING_PLAN.md §1.2 |
| 25 | CI/CD | No frontend build in CI | **HIGH** | ci.yml |
| 26 | Docs | `README.md` has placeholder text | **HIGH** | README.md |
| 27 | Docs | `SUBMISSION.md` has placeholder text | **HIGH** | SUBMISSION.md |

---

## PHASE 1: BACKEND FOUNDATION (Must complete first)

### BE-1.1: Enums (PHP 8.1)
- [ ] Create `backend/app/Enums/TicketStatus.php` with cases: OPEN, PENDING, RESOLVED, CLOSED. Add `label()` and `values()` methods.
- [ ] Create `backend/app/Enums/TicketPriority.php` with cases: LOW, MEDIUM, HIGH, URGENT. Add `label()` and `values()` methods.
- [ ] Create `backend/app/Enums/UserRole.php` with cases: ADMIN, AGENT, CUSTOMER. Add `label()` and `values()` methods.
- [ ] Create `backend/app/Enums/ActivityAction.php` with cases: CREATED, ASSIGNED, STATUS_CHANGED, PRIORITY_CHANGED, REPLIED, INTERNAL_NOTE, RESOLVED, CLOSED.
- [ ] Verify all enum files are referenced in FormRequest validation rules using `implode(',', Enum::values())`.

### BE-1.2: Migrations (Strictly ordered)
- [ ] `0001_01_01_000000_create_organizations_table.php`: `id`, `name`, `slug` (varchar 64, unique), `timestamps()`.
- [ ] `0001_01_01_000001_create_users_table.php`: `id`, `organization_id` (FK, cascade), `name`, `email`, `password`, `role` (enum string), `remember_token` (nullable), `timestamps()`. Add composite index on `(organization_id, role)` and index on `email`.
- [ ] `0001_01_01_000002_create_tickets_table.php`: `id`, `organization_id` (FK, cascade), `requester_id` (FK, restrict), `assignee_id` (FK, nullable, set null), `subject`, `description` (nullable, text), `status` (enum string, default 'open'), `priority` (enum string, default 'medium'), `tags` (json, nullable), `timestamps()`. Add composite index on `(organization_id, status, priority, assignee_id)` and indexes on `requester_id`, `assignee_id`, `status`, `priority`, `created_at`.
- [ ] `0001_01_01_000003_create_comments_table.php`: `id`, `ticket_id` (FK, cascade), `author_id` (FK, restrict), `body` (text), `is_internal` (boolean, default false), `timestamps()`. **NO `organization_id` column** (comments inherit tenant via ticket). Add composite index on `(ticket_id, created_at)` and index on `author_id` and `is_internal`.
- [ ] `0001_01_01_000004_create_sla_policies_table.php`: `id`, `organization_id` (FK, cascade), `priority` (enum string), `response_minutes` (unsigned int, default 0), `resolution_minutes` (unsigned int, default 0), `timestamps()`. Add unique index on `(organization_id, priority)`.
- [ ] `0001_01_01_000005_create_activity_logs_table.php`: `id`, `ticket_id` (FK, cascade), `actor_id` (FK, nullable, set null), `action` (enum string), `meta` (json, nullable), `created_at` (timestamp, no `updated_at`). Add composite index on `(ticket_id, created_at)` and index on `actor_id`.
- [ ] Verify all `down()` methods drop tables in reverse order.
- [ ] Run `php artisan migrate` and confirm zero errors.

### BE-1.3: Models & Relationships
- [ ] `Organization.php`: `hasMany` users, tickets, slaPolicies. Auto-generate `slug` from `name` on `creating` event if empty.
- [ ] `User.php`: `BelongsToTenant` trait, `HasApiTokens`. `belongsTo` organization. `hasMany` ticketsAsRequester, ticketsAsAssignee, comments, activityLogs. `casts` role to `UserRole`. Add `isAdmin()`, `isAgent()`, `isCustomer()` helper methods.
- [ ] `Ticket.php`: `BelongsToTenant` trait. `belongsTo` requester, assignee (nullable). `hasMany` comments, activityLogs. `casts` status to `TicketStatus`, priority to `TicketPriority`, tags to `array`. Add `scopeFilter($query, array $filters)` with status, priority, assignee, requester, q (search on subject+description).
- [ ] `Comment.php`: `belongsTo` ticket, author. `casts` is_internal to `boolean`. **NO `BelongsToTenant` trait** (tenant isolation via ticket relationship).
- [ ] `SlaPolicy.php`: `BelongsToTenant` trait. `belongsTo` organization. `casts` response_minutes, resolution_minutes to `integer`.
- [ ] `ActivityLog.php`: `$timestamps = false`. `belongsTo` ticket, actor (nullable). `casts` action to `ActivityAction`, meta to `array`, created_at to `datetime`.
- [ ] Verify all model factories exist and generate valid data.

### BE-1.4: Traits & Scopes (Tenant Isolation)
- [ ] `BelongsToTenant.php` trait: Add `TenantScope` global scope in `bootBelongsToTenant`. Set `organization_id` from `auth()->user()->organization_id` on `creating` event.
- [ ] `TenantScope.php`: Apply `where($table.organization_id, auth()->user()->organization_id)` to all queries when authenticated.
- [ ] Verify `TenantScope` does NOT break console commands or seeders by using `Model::withoutGlobalScope(TenantScope::class)` in seeders.
- [ ] Verify `Organization` model does NOT use `BelongsToTenant` (no org_id needed on itself).

### BE-1.5: Services (Business Logic Layer)
- [ ] `TicketService.php`: `create(array $data, User $user)` — sets requester_id, organization_id, status=OPEN, creates activity log. `update(Ticket $ticket, array $data, User $user)` — tracks old status/priority/assignee, calls `ActivityLogService` for changes. `assign(Ticket $ticket, ?int $assigneeId, User $user)` — updates assignee, logs activity. `getTicketsForUser(User $user, array $filters)` — applies role-based scope (customer sees own only) + filter scope.
- [ ] `CommentService.php`: `create(Ticket $ticket, array $data, User $user)` — enforces `is_internal=false` for customers, creates comment, logs activity as 'replied' or 'internal_note'.
- [ ] `ActivityLogService.php`: `static log(Ticket $ticket, string $action, ?User $user, array $meta = [])` — creates activity log row.
- [ ] `DashboardService.php`: `getStats(User $user)` — counts total, open, pending, resolved, closed, high, urgent, unassigned tickets (role-scoped). `getRecentActivity(User $user, int $limit = 10)` — fetches activity logs with actor and ticket, role-scoped.

### BE-1.6: Policies (Authorization Layer)
- [ ] `TicketPolicy.php`: `viewAny` = true (scope handles). `view` — customer sees own only, admin/agent see org. `create` = true. `update` — customer own only, admin/agent any in org. `delete` — admin only. `assign` — admin/agent only.
- [ ] `CommentPolicy.php`: `view` — internal notes hidden from customers. `create` = true (if user can view ticket). `delete` — author within 15 min OR admin.
- [ ] `UserPolicy.php`: `viewAny` = true. `view` — customer sees self, admin/agent see org. `create` = admin only. `update` = admin only in same org.
- [ ] Register all policies in `AuthServiceProvider.php` with `$policies` map.
- [ ] Verify `AuthServiceProvider` is registered in `config/app.php` providers array (or auto-discovered in Laravel 11).

### BE-1.7: Form Requests (Validation Layer)
- [ ] `LoginRequest.php`: `email` required|email, `password` required|string.
- [ ] `RegisterRequest.php`: `organization_name` required|string|min:2|max:255, `name` required|string|min:2|max:255, `email` required|email|max:255|unique:users,email, `password` required|confirmed|min:8.
- [ ] `StoreTicketRequest.php`: `subject` required|string|min:3|max:255, `description` nullable|string, `priority` nullable|in:low,medium,high,urgent, `tags` nullable|array, `tags.*` string|max:50, `requester_id` sometimes|integer|exists:users,id.
- [ ] `UpdateTicketRequest.php`: `subject` sometimes|string|min:3|max:255, `description` sometimes|nullable|string. For admin/agent only: `status` sometimes|in:open,pending,resolved,closed, `priority` sometimes|in:low,medium,high,urgent, `assignee_id` sometimes|nullable|integer|exists:users,id, `tags` sometimes|nullable|array, `tags.*` string|max:50.
- [ ] `TicketFilterRequest.php`: `status` nullable|in:open,pending,resolved,closed, `priority` nullable|in:low,medium,high,urgent, `assignee` nullable|integer, `requester` nullable|integer, `q` nullable|string|max:255, `sort` nullable|in:created_at,updated_at,priority,status, `direction` nullable|in:asc,desc, `page` nullable|integer|min:1, `per_page` nullable|integer|min:1|max:100.
- [ ] `StoreCommentRequest.php`: `body` required|string|min:1, `is_internal` sometimes|boolean. Override `validated()` to force `is_internal=false` for non-admin/agent.
- [ ] `StoreUserRequest.php`: `name` required|string|min:2|max:255, `email` required|email|max:255|unique:users,email, `password` required|confirmed|min:8, `role` required|in:admin,agent,customer. `authorize()` returns `$this->user()->isAdmin()`.

### BE-1.8: API Resources (Response Formatting)
- [ ] `TicketResource.php`: `id`, `subject`, `description`, `status` (value), `priority` (value), `tags`, `requester` (whenLoaded, UserResource), `assignee` (whenLoaded, UserResource), `comments` (whenLoaded, CommentResource), `organization_id`, `created_at` (ISO8601), `updated_at` (ISO8601).
- [ ] `TicketCollection.php`: wraps `data` array with `meta` (current_page, per_page, total, last_page).
- [ ] `CommentResource.php`: `id`, `body`, `is_internal`, `author` (whenLoaded, UserResource), `created_at` (ISO8601).
- [ ] `UserResource.php`: `id`, `name`, `email`, `role` (value), `organization_id`, `organization` (whenLoaded, OrganizationResource), `created_at` (ISO8601).
- [ ] `OrganizationResource.php`: `id`, `name`, `slug`.
- [ ] `DashboardStatsResource.php`: `stats` (total, open, pending, resolved, closed, high, urgent, unassigned), `recent_activity` (array of activity logs).

### BE-1.9: Controllers (HTTP Layer)
- [ ] `AuthController.php`: `register(RegisterRequest)` — creates org + user (admin role) + token, returns 201. `login(LoginRequest)` — validates credentials, returns token + user. `logout()` — revokes token, returns message. `me()` — returns current user with organization.
- [ ] `TicketController.php`: `index(TicketFilterRequest)` — paginated list with filters, sort. `store(StoreTicketRequest)` — rejects requester_id for non-admin/agent, creates via TicketService. `show(Ticket)` — loads requester, assignee, comments.author. `update(UpdateTicketRequest, Ticket)` — role-restricted fields, updates via TicketService. `destroy(Ticket)` — admin only, returns 204. `assign(Request, Ticket)` — validates assignee_id, updates via TicketService.
- [ ] `CommentController.php`: `index(Ticket)` — paginated comments, hides internal for customers. `store(StoreCommentRequest, Ticket)` — creates via CommentService. `destroy(Comment)` — author within 15 min or admin.
- [ ] `UserController.php`: `index()` — paginated users, role-filtered (customer sees self, agent sees customers, admin sees all). `store(StoreUserRequest)` — admin only, creates user in same org.
- [ ] `DashboardController.php`: `index()` — returns stats + recent activity via DashboardService.

### BE-1.10: Routes (api.php)
- [ ] Public: `POST /api/register` → AuthController@register. `POST /api/login` → AuthController@login.
- [ ] Authenticated group (middleware `auth:sanctum` + `tenant`):
  - `POST /api/logout` → AuthController@logout
  - `GET /api/me` → AuthController@me
  - `GET /api/users` → UserController@index
  - `POST /api/users` → UserController@store
  - `GET /api/tickets` → TicketController@index
  - `POST /api/tickets` → TicketController@store
  - `GET /api/tickets/{ticket}` → TicketController@show
  - `PUT /api/tickets/{ticket}` → TicketController@update
  - `DELETE /api/tickets/{ticket}` → TicketController@destroy
  - `PATCH /api/tickets/{ticket}/assign` → TicketController@assign
  - `GET /api/tickets/{ticket}/comments` → CommentController@index
  - `POST /api/tickets/{ticket}/comments` → CommentController@store
  - `DELETE /api/comments/{comment}` → CommentController@destroy
  - `GET /api/dashboard` → DashboardController@index
- [ ] Verify model binding for `Ticket` and `Comment` uses `TenantScope` (will auto-scope). No additional route model binding needed.

### BE-1.11: Middleware Configuration
- [ ] `EnsureTenantContext.php`: Check `auth()->user()->organization_id` exists. Return 403 if missing.
- [ ] Register `tenant` middleware alias in `bootstrap/app.php` using `->withMiddleware()` closure.
- [ ] Verify `auth:sanctum` is configured in `config/sanctum.php` with `stateful` domains including `127.0.0.1:5173`.
- [ ] Verify CORS config (`config/cors.php`) allows `http://127.0.0.1:5173` with `allowed_origins` and `supports_credentials`.

### BE-1.12: Error Handling (bootstrap/app.php)
- [ ] `AuthenticationException` → 401 JSON with message "Unauthenticated." for `api/*` routes.
- [ ] `AuthorizationException` → 403 JSON with exception message for `api/*` routes.
- [ ] `ModelNotFoundException` → 404 JSON with message "Resource not found." for `api/*` routes.
- [ ] Generic `Throwable` → 500 JSON with message "An unexpected error occurred." for `api/*` routes.
- [ ] Ensure validation errors (422) return `{message: "...", errors: {...}}` format (Laravel default).

---

## PHASE 2: BACKEND TESTING (Must pass before frontend integration)

### BE-2.1: Feature Tests
- [ ] `AuthTest.php`: Test `POST /api/register` returns 201 with token and user. Test `POST /api/login` returns 200 with token. Test `POST /api/login` with wrong password returns 401. Test `POST /api/logout` returns 200. Test `GET /api/me` returns user data. Test `GET /api/me` without token returns 401.
- [ ] `TenantIsolationTest.php`: Create Org A with user+ticket. Create Org B with user+ticket. User A fetches `/api/tickets` — assert Org B ticket NOT present. User A tries `GET /api/tickets/{org_b_ticket_id}` — assert 403 or 404. User A creates comment on Org B ticket — assert 403. Test cross-org user listing isolation.
- [ ] `TicketTest.php`: Test customer can create ticket. Test customer can view own ticket. Test customer cannot view other customer's ticket. Test customer cannot update status. Test customer cannot assign ticket. Test admin can update any ticket in org. Test agent can update any ticket in org. Test `GET /api/tickets` with filters (status, priority, q). Test pagination works. Test `PATCH /api/tickets/{id}/assign` works for admin/agent. Test `DELETE /api/tickets/{id}` works for admin only (403 for agent/customer).
- [ ] `CommentTest.php`: Test customer can post public comment. Test customer cannot post internal comment (forced to false). Test customer cannot see internal comments. Test admin/agent can see internal comments. Test agent can post internal comment. Test comment author can delete own comment within 15 min. Test admin can delete any comment. Test non-author cannot delete comment after 15 min.
- [ ] `UserTest.php`: Test admin can list all users in org. Test agent lists only customers. Test customer lists only self. Test admin can create user. Test agent cannot create user (403). Test customer cannot create user (403).
- [ ] `DashboardTest.php`: Test dashboard returns stats for admin. Test dashboard returns only customer-scoped stats for customer. Test recent activity is returned.

### BE-2.2: Unit Tests (Optional but recommended)
- [ ] `TicketPolicyTest.php`: Test each policy method with all three roles.
- [ ] `TicketServiceTest.php`: Test `create` sets correct defaults. Test `update` logs activity on status change. Test `assign` logs activity.

### BE-2.3: Test Infrastructure
- [ ] `TestCase.php`: Helper `createUser(string $role, ?Organization $org)` and `actingAsUser(User $user)` using `Sanctum::actingAs()`.
- [ ] `phpunit.xml`: Uses SQLite in-memory database for tests. `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`.
- [ ] `.env.testing`: Separate test environment with SQLite.
- [ ] Run `php artisan test` — all tests must pass.

---

## PHASE 3: FRONTEND FOUNDATION (Must complete after backend auth works)

### FE-3.1: Project Setup & Dependencies
- [ ] `package.json`: Include `react`, `react-dom`, `react-router-dom`, `axios`, `lucide-react`, `clsx`, `tailwind-merge`.
- [ ] `vite.config.js`: Port 5173, proxy `/api` to `http://127.0.0.1:8000`.
- [ ] `tailwind.config.js`: Content paths `./index.html`, `./src/**/*.{js,ts,jsx,tsx}`. Extend colors with `primary` palette.
- [ ] `postcss.config.js`: Tailwind + autoprefixer.
- [ ] `.env.example`: `VITE_API_URL=http://127.0.0.1:8000/api`.
- [ ] `index.css`: `@tailwind` directives + base body styles.
- [ ] `jsconfig.json`: Path aliases for `src/*`.
- [ ] `main.jsx`: StrictMode, renders `<App />` into root.

### FE-3.2: API Layer
- [ ] `api/api.js`: Axios instance with baseURL from env. Request interceptor adds `Authorization: Bearer *** from localStorage. Response interceptor handles 401 by clearing token and redirecting to `/login`.
- [ ] `api/auth.js`: `register(data)`, `login(data)`, `logout()`, `me()`.
- [ ] `api/tickets.js`: `getTickets(params)`, `getTicket(id)`, `createTicket(data)`, `updateTicket(id, data)`, `deleteTicket(id)`, `assignTicket(id, assigneeId)`.
- [ ] `api/comments.js`: `getComments(ticketId, params)`, `createComment(ticketId, data)`, `deleteComment(id)`.
- [ ] `api/users.js`: `getUsers(params)`, `createUser(data)`.
- [ ] `api/dashboard.js`: `getDashboardStats()`.

### FE-3.3: Utilities
- [ ] `utils/constants.js`: `API_BASE_URL`, `ROLES`, `STATUSES`, `PRIORITIES`, `STATUS_COLORS` (open=green, pending=yellow, resolved=blue, closed=gray), `PRIORITY_COLORS` (low=gray, medium=blue, high=orange, urgent=red).
- [ ] `utils/dateUtils.js`: `formatRelativeDate(dateString)` — returns "just now", "5m ago", "2h ago", "3d ago", or locale date.
- [ ] `utils/helpers.js`: `cn(...inputs)` using `clsx` + `tailwind-merge`. `getInitials(name)` — returns up to 2 uppercase initials.
- [ ] `utils/validators.js`: `isValidEmail(email)`, `minLength(str, n)`, `required(str)`.

### FE-3.4: State Management (Contexts)
- [ ] `context/AuthContext.jsx`: `user`, `isLoading`, `login(userData, token)`, `logout()`, `fetchUser()`. On mount, reads token from localStorage, calls `me()`. On 401, clears token and user.
- [ ] `context/ToastContext.jsx`: `toasts` array, `addToast(message, type)`, `removeToast(id)`. Auto-dismiss after 3 seconds. Fixed bottom-right position.

### FE-3.5: Routing & Guards
- [ ] `router/routes.jsx`: BrowserRouter with route definitions. Public routes: `/login`, `/register`. Protected routes: `/`, `/dashboard`, `/tickets`, `/tickets/new`, `/tickets/:id`, `/tickets/:id/edit`. Admin route: `/settings` (optional, if time permits).
- [ ] `router/guards/AuthGuard.jsx`: If `isLoading`, show full-screen Spinner. If `!user`, redirect to `/login`. Otherwise render `<Outlet />`.
- [ ] `router/guards/GuestGuard.jsx`: If `isLoading`, show full-screen Spinner. If `user`, redirect to `/dashboard`. Otherwise render `<Outlet />`.
- [ ] `router/guards/RoleGuard.jsx`: If `user.role` not in `allowedRoles`, redirect to `/dashboard`. Otherwise render `children`.
- [ ] `App.jsx`: Wraps with `AuthProvider`, `ToastProvider`, `RouterProvider`.

---

## PHASE 4: FRONTEND PAGES & COMPONENTS (Must complete after routing works)

### FE-4.1: Layout Components
- [ ] `components/layout/AuthLayout.jsx`: Centered layout for login/register. Minimal header/logo.
- [ ] `components/layout/DashboardLayout.jsx`: Sidebar + Header + Main content area. `min-h-screen bg-gray-50`.
- [ ] `components/layout/Header.jsx`: Logo "PulseDesk", user avatar + name + role badge, logout button. Sticky top, white background, shadow.
- [ ] `components/layout/Sidebar.jsx`: Navigation links: Dashboard (`/`), All Tickets (`/tickets`), New Ticket (`/tickets/new`). Active link highlight. Collapsible on mobile (hamburger menu).
- [ ] `components/layout/UserMenu.jsx`: Dropdown from avatar. Shows user name, email, role. Logout option. Close on outside click.

### FE-4.2: Common Components
- [ ] `components/common/Button.jsx`: Variants: primary (blue), secondary (gray), danger (red), ghost (transparent). Sizes: sm, md, lg. Props: `isLoading`, `fullWidth`, `disabled`.
- [ ] `components/common/Input.jsx`: Label, input field, error message. Types: text, email, password, number. Required asterisk.
- [ ] `components/common/TextArea.jsx`: Label, textarea, error message. Props: `rows`, `maxLength`.
- [ ] `components/common/Modal.jsx`: Overlay + centered dialog. Close button, title slot, content slot, footer slot. Close on Escape key and overlay click.
- [ ] `components/common/Spinner.jsx`: SVG spinner. Props: `size` (sm, md, lg), `fullScreen` (centered overlay).
- [ ] `components/common/Toast.jsx`: Colored notification bar (info=blue, success=green, error=red, warning=yellow). Auto-dismiss progress bar.

### FE-4.3: Authentication Pages
- [ ] `pages/LoginPage.jsx`: Email + password form. Submit calls `login()`, stores token, redirects to `/dashboard`. Error toast on failure. Link to `/register`.
- [ ] `pages/RegisterPage.jsx`: Organization name, name, email, password, password confirmation. Submit calls `register()`, auto-login, redirects to `/dashboard`. Error toast on failure. Link to `/login`.

### FE-4.4: Dashboard Page
- [ ] `pages/DashboardPage.jsx`: Fetches dashboard stats. Layout: page title + StatsCards grid + ActivityFeed + RecentTickets.
- [ ] `components/dashboard/StatsCards.jsx`: Grid of 8 cards. Each card: icon, number, label. Colors based on metric type (urgent=red, open=green, etc.).
- [ ] `components/dashboard/ActivityFeed.jsx`: List of activity items. Each: action icon, description text (e.g., "Status changed from Open to Pending"), actor name, relative timestamp. Max 10 items.
- [ ] `components/dashboard/RecentTickets.jsx`: Table of 5 most recent tickets. Columns: subject, status badge, updated time. Link to detail page.

### FE-4.5: Ticket List Page
- [ ] `pages/TicketListPage.jsx`: Page title + "New Ticket" button + TicketFilters + TicketList + Pagination.
- [ ] `components/tickets/TicketFilters.jsx`: Search input (debounced, 300ms), Status dropdown (all/open/pending/resolved/closed), Priority dropdown (all/low/medium/high/urgent), Assignee dropdown (agents only), Clear Filters button. Filters update URL query params or state.
- [ ] `components/tickets/TicketList.jsx`: Responsive table or card grid. Columns: ID, Subject, Requester, Assignee (or "Unassigned"), Status badge, Priority badge, Created date, Tags. Empty state: "No tickets found." Click row navigates to `/tickets/:id`.
- [ ] `components/tickets/TicketCard.jsx`: Card variant for mobile. Same info as table row in card format.
- [ ] `components/tickets/StatusBadge.jsx`: Colored pill badge based on status. Uses `STATUS_COLORS` from constants.
- [ ] `components/tickets/PriorityBadge.jsx`: Colored pill badge based on priority. Uses `PRIORITY_COLORS` from constants.
- [ ] `components/tickets/TagList.jsx`: Horizontal flex of small colored pills. If no tags, render nothing.

### FE-4.6: Ticket Detail Page
- [ ] `pages/TicketDetailPage.jsx`: Two-column layout on desktop (main + sidebar), single column on mobile. Main: Ticket header + CommentThread + CommentForm. Sidebar: ticket metadata, status/priority/assignee editors (admin/agent only), tags.
- [ ] `components/tickets/TicketForm.jsx`: Reusable form. Fields: subject (text), description (textarea), priority (select), status (select), assignee (select), tags (multi-select or tag input). Customer sees only subject and description. Admin/agent sees all fields. Used in NewTicketPage and EditTicketPage.
- [ ] `components/comments/CommentThread.jsx`: Reverse chronological list of comments. Groups by date. Internal notes have yellow background + "Internal Note" label. Customer replies have left alignment. Agent replies have right alignment. Shows author avatar, name, role, timestamp.
- [ ] `components/comments/CommentForm.jsx`: Textarea + submit button. Admin/agent see "Internal note" checkbox toggle. Character counter. Submit calls `createComment()`.
- [ ] `components/comments/CommentItem.jsx`: Single comment. Avatar, author name, role badge, timestamp, body. Delete button (author within 15 min or admin).

### FE-4.7: New/Edit Ticket Pages
- [ ] `pages/NewTicketPage.jsx`: Page title "Create Ticket". TicketForm with empty defaults. Submit calls `createTicket()`, redirects to `/tickets` on success. Toast on success/error.
- [ ] `pages/EditTicketPage.jsx`: Page title "Edit Ticket". Fetches ticket by ID, pre-fills form. Submit calls `updateTicket()`, redirects to `/tickets/:id` on success. 404 handling if ticket not found. Role-based field disabling (customer cannot edit status/priority/assignee).

### FE-4.8: User Components
- [ ] `components/users/UserAvatar.jsx`: Circular div with initials. Background color derived from name hash. Size variants: sm (32px), md (40px), lg (48px).
- [ ] `components/users/UserSelect.jsx`: Dropdown select populated with `getUsers()`. Filter by role (e.g., only agents). Shows avatar + name per option. "Unassigned" option for assignee.

### FE-4.9: Hooks (Custom)
- [ ] `hooks/useAuth.js`: Consumes `AuthContext`.
- [ ] `hooks/useTickets.js`: Fetches tickets with filters. Returns `{tickets, meta, isLoading, error, filters, updateFilters, goToPage, refetch}`.
- [ ] `hooks/useTicket.js`: Fetches single ticket by ID. Returns `{ticket, isLoading, error, refetch}`.
- [ ] `hooks/useComments.js`: Fetches comments for ticket. Returns `{comments, isLoading, error, addComment, refetch}`.
- [ ] `hooks/useUsers.js`: Fetches users. Returns `{users, isLoading, error, refetch}`.
- [ ] `hooks/useDashboard.js`: Fetches dashboard stats. Returns `{stats, activity, isLoading, error, refetch}`.
- [ ] `hooks/useLocalStorage.js`: `getItem(key)`, `setItem(key, value)`, `removeItem(key)`. Syncs with React state.

---

## PHASE 5: TESTING (Backend + Frontend Integration)

### T-5.1: Backend Feature Tests (see BE-2.1 for full list)
- [ ] `tests/Feature/AuthTest.php`: All auth flows pass.
- [ ] `tests/Feature/TenantIsolationTest.php`: Cross-org leakage is blocked.
- [ ] `tests/Feature/TicketTest.php`: CRUD, filters, search, assignment, role restrictions.
- [ ] `tests/Feature/CommentTest.php`: Internal notes hidden, role restrictions, deletion.
- [ ] `tests/Feature/UserTest.php`: User listing, creation, role restrictions.
- [ ] `tests/Feature/DashboardTest.php`: Stats accuracy, role scoping.
- [ ] Run `php artisan test` — **ALL GREEN**.

### T-5.2: Frontend Manual QA Protocol
- [ ] Login as Admin (`admin@acme.test` / `password`). Dashboard loads with stats. Can see all tickets. Can create internal notes. Can assign tickets. Can change status/priority. Can delete comments. Can create users.
- [ ] Login as Agent (`agent@acme.test` / `password`). Dashboard loads. Can see all org tickets. Can reply. Can create internal notes. Can assign tickets. Can change status/priority. CANNOT delete tickets. CANNOT create users.
- [ ] Login as Customer (`customer@acme.test` / `password`). Dashboard loads with own tickets only. Can create ticket. Can reply to own ticket. CANNOT see internal notes. CANNOT change status/priority. CANNOT assign. CANNOT see other customers' tickets.
- [ ] Cross-browser: Test in Chrome and Firefox. Mobile responsive check (sidebar collapses, tables stack).
- [ ] Token expiry: Delete localStorage token, refresh page — redirected to login.

### T-5.3: Integration Tests
- [ ] End-to-end: Create ticket → assign to agent → add internal note → change status → verify activity log → verify customer sees only public comment.
- [ ] Search: Create ticket with unique subject. Search for it in ticket list. Assert found.
- [ ] Filters: Apply status=open filter. Assert only open tickets shown. Clear filters. Assert all shown.
- [ ] Pagination: Create 25 tickets. Set per_page=10. Assert page 1 has 10, page 2 has 10, page 3 has 5.

---

## PHASE 6: CI/CD & DEPLOYMENT

### CI-6.1: GitHub Actions Workflow (`.github/workflows/ci.yml`)
- [ ] Trigger on `push` to `main` and `pull_request`.
- [ ] **Backend job:**
  - Checkout code.
  - Setup PHP 8.2 with extensions: pdo, pdo_mysql, mbstring, xml, ctype, json, bcmath.
  - Setup MySQL 8 service container. `MYSQL_DATABASE: pulsedesk_test`, `MYSQL_ROOT_PASSWORD: root`.
  - `composer install --no-interaction --prefer-dist`.
  - Copy `.env.example` to `.env`, generate key.
  - Set DB_HOST=127.0.0.1, DB_DATABASE=pulsedesk_test, DB_USERNAME=root, DB_PASSWORD=root.
  - `php artisan migrate --force`.
  - `php artisan test`.
- [ ] **Frontend job:**
  - Checkout code.
  - Setup Node.js 20.
  - `cd frontend && npm install`.
  - `cd frontend && npm run build` (verifies Vite build succeeds without errors).
- [ ] **Optional:** Add ESLint check (`npm run lint` if configured).
- [ ] Verify pipeline is **GREEN** on the Actions tab.

### CI-6.2: Environment Configuration
- [ ] `backend/.env.example`: Contains all required vars: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`, `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `SANCTUM_STATEFUL_DOMAINS`.
- [ ] `frontend/.env.example`: `VITE_API_URL=http://127.0.0.1:8000/api`.
- [ ] `backend/.env.testing`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`.
- [ ] Both `.env.example` files are committed to repo.
- [ ] Actual `.env` files are in `.gitignore`.

---

## PHASE 7: SEEDERS & DEMO DATA

### S-7.1: Database Seeder
- [ ] `database/seeders/DatabaseSeeder.php`: Calls `DemoSeeder::class`.
- [ ] `database/seeders/DemoSeeder.php`:
  - Create Organization: `Acme Corp` with slug `acme-corp`.
  - Create Users: Admin (`admin@acme.test`), Agent (`agent@acme.test`), Customer (`customer@acme.test`). All passwords: `password` (hashed).
  - Create 10-12 tickets with varying status, priority, assignee (some unassigned).
  - Add 2-3 comments per ticket (mix of public and internal).
  - Add activity logs for status changes and assignments.
- [ ] Verify `php artisan migrate --seed` runs successfully from a fresh clone.
- [ ] Verify login with demo credentials works immediately after seeding.

---

## PHASE 8: DOCUMENTATION & SUBMISSION

### D-8.1: README.md (Complete)
- [ ] Project name: `PulseDesk — Forge 2 / Edition 1`.
- [ ] Description: Multi-tenant SaaS helpdesk built by orchestrating Hermes + OpenClaw over Slack.
- [ ] Stack: Laravel 11, PHP 8.2+, MySQL 8, Laravel Sanctum, React 19, Vite, Tailwind CSS.
- [ ] **Exact run steps** (judge will run from fresh clone):
  1. `cd backend && cp .env.example .env` — set DB credentials.
  2. `composer install`.
  3. `php artisan key:generate`.
  4. `php artisan migrate --seed`.
  5. `php artisan serve` (port 8000).
  6. `cd frontend && cp .env.example .env`.
  7. `npm install`.
  8. `npm run dev` (port 5173).
- [ ] Demo logins: `admin@acme.test / password`, `agent@acme.test / password`, `customer@acme.test / password`.
- [ ] Live URL: placeholder or actual deployed URL.
- [ ] Evidence locations: `agents/`, `agent-log.md`, `sprints/`, `slack-export/`, `evidence/screenshots/`.
- [ ] Model credits: Hermes (planning), OpenClaw (coding).

### D-8.2: SUBMISSION.md (Complete)
- [ ] All checklist items ticked:
  - [ ] Repo is public, named `forge2-parassingh`.
  - [ ] README has exact run steps; `php artisan migrate --seed` works.
  - [ ] Backend = Laravel 11 + MySQL; Frontend = React 19 + Vite + Tailwind.
  - [ ] Multi-tenancy verified: Org A cannot see Org B data.
  - [ ] Hermes config committed: `agents/hermes/hermes-config.yaml` (secrets redacted).
  - [ ] OpenClaw config committed: `agents/openclaw/openclaw.json` (secrets redacted).
  - [ ] `agent-log.md` shows real human→Hermes→OpenClaw loop.
  - [ ] `sprints/` has 5 sprint docs.
  - [ ] Slack proof in `slack-export/` or `slack-export/screenshots/`.
  - [ ] App/agents/CI screenshots in `evidence/screenshots/`.
  - [ ] `.github/workflows/ci.yml` present + green run on Actions tab.
  - [ ] PRs merged by human; commit authors are the agents.
  - [ ] All model calls went through EastRouter.
  - [ ] Models used and sprint count filled in.

### D-8.3: Agent Evidence
- [ ] `agent-log.md`: Updated with every session. Format: Date → Human instruction → Hermes plan → OpenClaw code → Verification result.
- [ ] `agents/hermes/hermes-config.yaml`: Committed with API keys/secrets redacted (replace with `***`).
- [ ] `agents/openclaw/openclaw.json`: Committed with secrets redacted.
- [ ] `sprints/sprint-01.md` through `sprint-05.md`: Each has Goal, User Stories, Technical Tasks, Acceptance Criteria, Files Expected, Risks, Estimated Complexity.
- [ ] `slack-export/screenshots/`: At least 3 screenshots showing Slack conversation with Hermes and OpenClaw.
- [ ] `evidence/screenshots/`: At least 5 screenshots: app dashboard, ticket list, ticket detail, CI green run, agents working.

---

## FINAL VERIFICATION CHECKLIST (Before Submitting)

### Functionality
- [ ] `php artisan migrate --seed` runs from a fresh clone with zero errors.
- [ ] `npm run dev` starts the frontend with zero errors.
- [ ] Login works for all three demo accounts.
- [ ] Customer can create a ticket and see it in their list.
- [ ] Agent can see all org tickets, assign tickets, change status/priority.
- [ ] Admin can create users, delete tickets, see everything.
- [ ] Internal notes are hidden from customers.
- [ ] Search and filters work on ticket list.
- [ ] Dashboard shows accurate stats.
- [ ] Activity log records ticket changes.
- [ ] Cross-tenant isolation: creating a second org + user + ticket, the first org cannot see the second org's data.

### Code Quality
- [ ] All backend feature tests pass (`php artisan test` — green).
- [ ] Frontend builds without errors (`npm run build` — success).
- [ ] GitHub Actions CI is green on the latest commit.
- [ ] No hardcoded secrets in source code.
- [ ] `.env` files are in `.gitignore`.
- [ ] `.env.example` files are committed with placeholder values.

### Documentation
- [ ] README.md is complete and accurate.
- [ ] SUBMISSION.md is fully filled out.
- [ ] agent-log.md shows the real agent loop.
- [ ] Sprint docs are complete (all 5).
- [ ] Screenshots exist in evidence/ and slack-export/.

### Submission
- [ ] Repo is public.
- [ ] Named correctly: `forge2-parassingh`.
- [ ] All files committed to git.
- [ ] No uncommitted changes.
- [ ] Latest commit is pushed to origin.
