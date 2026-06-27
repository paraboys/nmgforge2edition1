# PulseDesk — Implementation Tasks

> Each task is independently completable with clear files, acceptance criteria, and dependencies.

---

## Legend
- **Backend files**: `B:` prefix (e.g., `B:app/Models/User.php`)
- **Frontend files**: `F:` prefix (e.g., `F:src/pages/LoginPage.jsx`)
- **Dependencies**: list of task IDs that must be completed before this task
- **Effort**: estimated complexity (XS=1h, S=2h, M=4h, L=8h, XL=16h)

---

### Task B-001: Laravel Project Setup
**Objective:** Initialize Laravel 11 backend with Sanctum and tenant middleware scaffolding.
**Files to create:**
- `B:composer.json` (require sanctum)
- `B:config/sanctum.php` (stateful domains)
- `B:bootstrap/app.php` (middleware registration)
**Files to modify:**
- `B:.env.example` (add Sanctum placeholders)
**Dependencies:** none
**Acceptance Criteria:**
- `composer install` completes without errors
- `php artisan migrate` runs (Sanctum migrations present)
- API routes respond at `http://127.0.0.1:8000/api`
**Effort:** S

---

### Task B-002: Database Migrations
**Objective:** Create all database tables with proper foreign keys, indexes, and tenant columns.
**Files to create:**
- `B:database/migrations/0001_01_01_000000_create_organizations_table.php`
- `B:database/migrations/0001_01_01_000001_create_users_table.php`
- `B:database/migrations/0001_01_01_000002_create_tickets_table.php`
- `B:database/migrations/0001_01_01_000003_create_comments_table.php`
- `B:database/migrations/0001_01_01_000004_create_sla_policies_table.php`
- `B:database/migrations/0001_01_01_000005_create_activity_logs_table.php`
- `B:database/migrations/0001_01_01_000006_create_personal_access_tokens_table.php` (Sanctum)
**Dependencies:** B-001
**Acceptance Criteria:**
- All migrations run in order without errors
- Foreign keys and indexes are correct
- `php artisan migrate:status` shows all migrations as "Ran"
**Effort:** M

---

### Task B-003: Eloquent Models & Tenant Trait
**Objective:** Create all Eloquent models with relationships, casts, and tenant trait.
**Files to create:**
- `B:app/Models/Organization.php`
- `B:app/Models/User.php`
- `B:app/Models/Ticket.php`
- `B:app/Models/Comment.php`
- `B:app/Models/SlaPolicy.php`
- `B:app/Models/ActivityLog.php`
- `B:app/Traits/BelongsToTenant.php`
- `B:app/Scopes/TenantScope.php`
**Dependencies:** B-002
**Acceptance Criteria:**
- All models have correct relationships defined
- `BelongsToTenant` trait adds global scope
- `TenantScope` applies `organization_id` filter when authenticated
- Models can be created via factory/tinker
**Effort:** M

---

### Task B-004: Middleware
**Objective:** Create tenant context and scope enforcement middleware.
**Files to create:**
- `B:app/Http/Middleware/SetTenantContext.php`
- `B:app/Http/Middleware/EnsureTenantScope.php`
**Dependencies:** B-003
**Acceptance Criteria:**
- Middleware registered in `bootstrap/app.php`
- `SetTenantContext` sets tenant singleton after auth
- `EnsureTenantScope` returns 403 for cross-tenant access
- Middleware runs on all API routes except public auth
**Effort:** S

---

### Task B-005: Policies
**Objective:** Create Laravel authorization policies for all resources.
**Files to create:**
- `B:app/Policies/TicketPolicy.php`
- `B:app/Policies/CommentPolicy.php`
- `B:app/Policies/UserPolicy.php`
- `B:app/Policies/OrganizationPolicy.php`
- `B:app/Providers/AuthServiceProvider.php` (register policies)
**Dependencies:** B-003
**Acceptance Criteria:**
- All policies registered in `AuthServiceProvider`
- `TicketPolicy` enforces role-based access (admin/agent/customer)
- `CommentPolicy` hides internal notes from customers
- `UserPolicy` restricts user creation to admin
- Policies prevent cross-tenant access
**Effort:** M

---

### Task B-006: Form Requests
**Objective:** Create validation form requests for all write operations.
**Files to create:**
- `B:app/Http/Requests/RegisterRequest.php`
- `B:app/Http/Requests/LoginRequest.php`
- `B:app/Http/Requests/StoreTicketRequest.php`
- `B:app/Http/Requests/UpdateTicketRequest.php`
- `B:app/Http/Requests/StoreCommentRequest.php`
- `B:app/Http/Requests/UpdateUserRequest.php`
**Dependencies:** B-005
**Acceptance Criteria:**
- All requests validate required fields correctly
- `UpdateTicketRequest` denies status/priority changes for customers
- `StoreCommentRequest` authorizes based on ticket policy
- `UpdateUserRequest` restricts to admin role
- Validation returns 422 with field errors on failure
**Effort:** S

---

### Task B-007: API Resources
**Objective:** Create JSON API resource transformers for consistent response format.
**Files to create:**
- `B:app/Http/Resources/OrganizationResource.php`
- `B:app/Http/Resources/UserResource.php`
- `B:app/Http/Resources/TicketResource.php`
- `B:app/Http/Resources/TicketListResource.php`
- `B:app/Http/Resources/CommentResource.php`
- `B:app/Http/Resources/ActivityLogResource.php`
**Dependencies:** B-003
**Acceptance Criteria:**
- All resources use `whenLoaded` for eager-loaded relations
- `TicketResource` includes nested comments and activity logs
- `TicketListResource` is lightweight (no nested comments)
- Timestamps in ISO 8601 format
**Effort:** S

---

### Task B-008: Auth Controller
**Objective:** Implement authentication endpoints (register, login, logout, me).
**Files to create:**
- `B:app/Http/Controllers/Api/AuthController.php`
**Dependencies:** B-006, B-007
**Acceptance Criteria:**
- POST /register creates org + admin user, returns token
- POST /login authenticates and returns token
- POST /logout revokes current token
- GET /me returns user with organization
- All endpoints return consistent JSON envelope
- Invalid credentials return 401
- Duplicate email returns 422
**Effort:** M

---

### Task B-009: Ticket Controller
**Objective:** Implement ticket CRUD with filtering, pagination, and role-based access.
**Files to create:**
- `B:app/Http/Controllers/Api/TicketController.php`
**Dependencies:** B-006, B-007, B-008
**Acceptance Criteria:**
- GET /tickets lists tenant-scoped tickets with filters, pagination
- POST /tickets creates ticket with requester set to auth user
- GET /tickets/{id} returns ticket with comments and activity logs
- PUT /tickets/{id} updates ticket (role-restricted)
- DELETE /tickets/{id} deletes ticket (admin only)
- Admin/agent see all org tickets; customer sees only own
- Search filters by subject and description
- Status, priority, assignee filters work correctly
**Effort:** L

---

### Task B-010: Comment Controller
**Objective:** Implement comment listing and creation on tickets.
**Files to create:**
- `B:app/Http/Controllers/Api/CommentController.php`
**Dependencies:** B-009
**Acceptance Criteria:**
- GET /tickets/{id}/comments lists comments on a ticket
- POST /tickets/{id}/comments creates comment
- Admin/agent can create internal notes (`is_internal=true`)
- Customer comments are forced to public (`is_internal=false`)
- Customer cannot comment on other customers' tickets
- Comments ordered by `created_at asc`
**Effort:** M

---

### Task B-011: User Controller
**Objective:** Implement user listing and creation (admin only).
**Files to create:**
- `B:app/Http/Controllers/Api/UserController.php`
**Dependencies:** B-008
**Acceptance Criteria:**
- GET /users lists users in same org (admin/agent only)
- POST /users creates agent or customer (admin only)
- Customer gets 403 on both endpoints
- Role filter works on list endpoint
- Password is hashed on creation
**Effort:** S

---

### Task B-012: API Routes
**Objective:** Wire all controllers to routes with proper middleware.
**Files to create/modify:**
- `B:routes/api.php`
**Dependencies:** B-008, B-009, B-010, B-011
**Acceptance Criteria:**
- Public routes: POST /register, POST /login
- Protected routes: all others under `auth:sanctum` + `tenant.context`
- Route model binding uses tenant-scoped queries
- Route names follow Laravel conventions
**Effort:** XS

---

### Task B-013: Demo Seeder
**Objective:** Create seeders for 1 org, 1 admin, 2 agents, 2 customers, ~12 tickets.
**Files to create:**
- `B:database/seeders/DatabaseSeeder.php`
- `B:database/seeders/DemoSeeder.php`
- `B:database/factories/OrganizationFactory.php`
- `B:database/factories/UserFactory.php`
- `B:database/factories/TicketFactory.php`
- `B:database/factories/CommentFactory.php`
**Dependencies:** B-003
**Acceptance Criteria:**
- `php artisan db:seed` creates all demo data
- 1 organization: Acme Corp (slug: acme-corp)
- 1 admin: admin@acme.test / password
- 2 agents: alice@acme.test, bob@acme.test / password
- 2 customers: jane@acme.test, john@acme.test / password
- ~12 tickets with varied status, priority, assignees, tags
- Seeders are idempotent (can run multiple times safely or use `php artisan migrate:fresh --seed`)
**Effort:** M

---

### Task B-014: Feature Tests — Auth
**Objective:** Test all authentication endpoints.
**Files to create:**
- `B:tests/Feature/AuthTest.php`
**Dependencies:** B-008, B-013
**Acceptance Criteria:**
- 10 tests passing (see TESTING_PLAN.md for list)
- Register, login, logout, me all tested
- Validation errors tested (duplicate email, short password, missing fields)
- Token issuance and revocation tested
**Effort:** M

---

### Task B-015: Feature Tests — Tenant Isolation
**Objective:** Test that cross-tenant access is impossible.
**Files to create:**
- `B:tests/Feature/TenantIsolationTest.php`
**Dependencies:** B-009, B-014
**Acceptance Criteria:**
- 6 tests passing (see TESTING_PLAN.md)
- Cross-org user cannot read/list/update/delete other org tickets
- Cross-org user cannot see other org comments or users
- All failures return 403 or 404 (not 500)
**Effort:** M

---

### Task B-016: Feature Tests — Tickets & Comments
**Objective:** Test ticket CRUD, comments, and role restrictions.
**Files to create:**
- `B:tests/Feature/TicketTest.php`
- `B:tests/Feature/CommentTest.php`
- `B:tests/Feature/RoleBasedAccessTest.php`
- `B:tests/Feature/UserTest.php`
**Dependencies:** B-009, B-010, B-011, B-015
**Acceptance Criteria:**
- TicketTest: 16 tests passing (CRUD, filters, pagination, search, role restrictions)
- CommentTest: 8 tests passing (public/internal, customer restrictions)
- RoleBasedAccessTest: 4 tests passing (admin/agent/customer permissions)
- UserTest: 7 tests passing (list, create, role restrictions)
- All tests use factories for setup
**Effort:** L

---

### Task F-001: Frontend Project Setup
**Objective:** Initialize React 19 + Vite + Tailwind CSS project.
**Files to create:**
- `F:package.json`
- `F:vite.config.js`
- `F:tailwind.config.js`
- `F:index.html`
- `F:src/main.jsx`
- `F:.env.example`
**Dependencies:** none
**Acceptance Criteria:**
- `npm install` completes without errors
- `npm run dev` starts dev server on `http://127.0.0.1:5173`
- Tailwind CSS classes are applied correctly
- React renders without console errors
**Effort:** S

---

### Task F-002: API Client & Services
**Objective:** Create Axios instance with auth interceptors and service layer.
**Files to create:**
- `F:src/api/api.js`
- `F:src/services/authService.js`
- `F:src/services/ticketService.js`
- `F:src/services/commentService.js`
- `F:src/services/userService.js`
**Dependencies:** F-001
**Acceptance Criteria:**
- Axios instance has base URL from `VITE_API_URL`
- Request interceptor attaches Bearer token from localStorage
- Response interceptor handles 401 by clearing auth and redirecting to login
- All services export async functions matching API endpoints
- Services handle errors by rejecting with readable messages
**Effort:** S

---

### Task F-003: Auth Context & Hooks
**Objective:** Create React context for auth state and custom data hooks.
**Files to create:**
- `F:src/context/AuthContext.jsx`
- `F:src/hooks/useAuth.js`
- `F:src/hooks/useTickets.js`
- `F:src/hooks/useTicket.js`
- `F:src/hooks/useComments.js`
- `F:src/hooks/useLocalStorage.js`
**Dependencies:** F-002
**Acceptance Criteria:**
- `AuthContext` provides `user`, `login`, `register`, `logout`, `isAdmin`, `isAgent`, `isCustomer`
- `useAuth` returns auth context values
- `useTickets` fetches tickets with filters, pagination, loading, error states
- `useTicket` fetches single ticket by ID
- `useComments` fetches comments and provides `addComment` function
- `useLocalStorage` persists values to localStorage
- Auth state persists across page reloads via localStorage
**Effort:** M

---

### Task F-004: Public Pages (Login, Register)
**Objective:** Create authentication pages with forms and validation.
**Files to create:**
- `F:src/pages/LoginPage.jsx`
- `F:src/pages/RegisterPage.jsx`
- `F:src/components/LoadingSpinner.jsx`
- `F:src/components/EmptyState.jsx`
**Dependencies:** F-003
**Acceptance Criteria:**
- Login page has email + password fields, submit button, link to register
- Register page has org name, name, email, password, confirm fields
- Client-side validation: required fields, email format, password match, min 8 chars
- On success: token stored, user state set, redirect to `/dashboard`
- On error: display API error message inline
- Loading state: button disabled + spinner during submit
- Already logged in users redirected to `/dashboard`
**Effort:** M

---

### Task F-005: Layout & Navigation
**Objective:** Create authenticated app shell with sidebar and navbar.
**Files to create:**
- `F:src/components/Layout.jsx`
- `F:src/components/Navbar.jsx`
- `F:src/components/Sidebar.jsx`
- `F:src/components/AuthGuard.jsx`
**Dependencies:** F-004
**Acceptance Criteria:**
- `Layout` wraps all authenticated pages with navbar + sidebar + main content area
- `Navbar` shows user name, role badge, logout button
- `Sidebar` shows navigation links: Dashboard, New Ticket, Admin (admin only)
- Active route highlighted in sidebar
- Mobile: sidebar collapses to hamburger menu
- Logout clears auth state and redirects to `/login`
- `AuthGuard` redirects unauthenticated users to `/login`
**Effort:** M

---

### Task F-006: Dashboard Page
**Objective:** Create ticket list with filtering, search, and pagination.
**Files to create:**
- `F:src/pages/DashboardPage.jsx`
- `F:src/components/TicketList.jsx`
- `F:src/components/TicketCard.jsx`
- `F:src/components/FilterBar.jsx`
- `F:src/components/SearchBar.jsx`
- `F:src/components/StatusBadge.jsx`
- `F:src/components/PriorityBadge.jsx`
- `F:src/components/UserAvatar.jsx`
**Dependencies:** F-005
**Acceptance Criteria:**
- Dashboard shows list of tickets from `useTickets` hook
- `TicketCard` displays subject, status badge, priority badge, requester, assignee, date
- Clicking card navigates to `/tickets/:id`
- `FilterBar` has status, priority, assignee dropdowns
- `SearchBar` filters by subject/description (debounced)
- Pagination shows prev/next and page numbers
- Empty state shows "No tickets found" with icon
- Admin/agent see all org tickets; customer sees own tickets
- Filters sync to URL query params (bookmarkable)
**Effort:** L

---

### Task F-007: Ticket Detail Page
**Objective:** Create ticket detail with conversation thread and actions.
**Files to create:**
- `F:src/pages/TicketDetailPage.jsx`
- `F:src/components/CommentThread.jsx`
- `F:src/components/CommentItem.jsx`
- `F:src/components/CommentForm.jsx`
- `F:src/components/TicketActions.jsx`
- `F:src/components/TicketMeta.jsx`
- `F:src/components/ConfirmDialog.jsx`
**Dependencies:** F-006
**Acceptance Criteria:**
- Page loads ticket by ID from `useTicket` hook
- Shows ticket subject, description, status, priority, requester, assignee, dates
- `CommentThread` lists all comments with author avatar, name, timestamp, body
- Internal notes have distinct styling (amber border, "Internal" badge)
- Customers cannot see internal notes
- `CommentForm` allows adding new comments; Ctrl+Enter submits
- Admin/agent can check "Internal note" checkbox
- `TicketActions` allows status change, priority change, assignee change (admin/agent only)
- Delete button visible to admin only with `ConfirmDialog`
- Back button returns to `/dashboard`
- Loading state while fetching
- 404 if ticket not found or unauthorized
**Effort:** L

---

### Task F-008: New & Edit Ticket Pages
**Objective:** Create forms for creating and editing tickets.
**Files to create:**
- `F:src/pages/NewTicketPage.jsx`
- `F:src/pages/EditTicketPage.jsx`
- `F:src/components/TicketForm.jsx`
- `F:src/components/Modal.jsx`
**Dependencies:** F-007
**Acceptance Criteria:**
- `NewTicketPage` has form with subject, description, priority, tags fields
- `EditTicketPage` pre-fills existing ticket data
- `TicketForm` handles both create and edit modes via `isEdit` prop
- Validation: subject required, description required, priority required
- Submit calls `ticketService.create()` or `update()`, then redirects to detail page
- Cancel button returns to previous page
- Status dropdown visible on edit (admin/agent only)
- Edit page accessible to admin/agent only (customer gets 403 or hidden UI)
**Effort:** M

---

### Task F-009: Admin Page
**Objective:** Create admin page for user management.
**Files to create:**
- `F:src/pages/AdminPage.jsx`
- `F:src/components/UserTable.jsx`
- `F:src/components/UserForm.jsx`
- `F:src/components/RoleGuard.jsx`
**Dependencies:** F-008
**Acceptance Criteria:**
- Admin page shows list of all org users in `UserTable`
- `UserForm` allows creating new agent or customer users
- Form fields: name, email, password, role (select)
- Admin-only access: non-admin redirected to `/dashboard`
- `RoleGuard` component reusable for any role-restricted route
- After creating user, table refreshes
- Success toast shown on create
**Effort:** M

---

### Task F-010: Error Handling & Polish
**Objective:** Add error boundaries, loading states, responsive design, and accessibility.
**Files to create:**
- `F:src/components/ErrorBoundary.jsx`
- `F:src/utils/constants.js`
- `F:src/utils/helpers.js`
- `F:src/utils/formatters.js`
- `F:src/pages/NotFoundPage.jsx`
**Dependencies:** F-009
**Acceptance Criteria:**
- `ErrorBoundary` catches React errors and shows fallback UI
- `NotFoundPage` shown for unknown routes with link to dashboard
- All pages have loading states (spinners, skeletons)
- All API errors show user-friendly messages (not raw JSON)
- Responsive: sidebar collapses on mobile, forms stack vertically, tables scroll horizontally
- Accessible: all inputs have labels, focus states visible, keyboard navigation works
- Colors: status and priority badges use consistent color palette
- Date formatting: relative times ("2 hours ago") or local format
**Effort:** M

---

### Task F-011: App Routing
**Objective:** Wire all pages with React Router and guards.
**Files to create/modify:**
- `F:src/App.jsx`
**Dependencies:** F-004, F-005, F-006, F-007, F-008, F-009, F-010
**Acceptance Criteria:**
- All routes defined in `ROUTING_PLAN.md` are implemented
- `AuthGuard` wraps protected routes
- `RoleGuard` wraps admin and admin/agent routes
- Public routes (`/login`, `/register`) accessible without auth
- Unknown routes show `NotFoundPage`
- Route params (`:id`) passed to page components via `useParams`
- Query params (`?status=open`) synchronized with filter state
**Effort:** S

---

### Task CI-001: GitHub Actions Workflow
**Objective:** Create CI pipeline that tests backend and builds frontend.
**Files to create/modify:**
- `.github/workflows/ci.yml`
**Dependencies:** B-016, F-011
**Acceptance Criteria:**
- Workflow triggers on PR and push to main
- Backend: PHP 8.2, MySQL 8 service, composer install, migrate, test
- Frontend: Node.js, npm install, npm run build
- All steps pass (green checkmark)
- Workflow file committed to repo
**Effort:** S

---

### Task DOC-001: Documentation & README
**Objective:** Update README with exact run steps and fill submission checklist.
**Files to modify:**
- `README.md`
- `SUBMISSION.md`
**Dependencies:** CI-001
**Acceptance Criteria:**
- README contains exact commands to run backend and frontend
- Demo logins listed (admin@acme.test, agent@acme.test, customer@acme.test)
- Stack versions listed (Laravel 11, React 19, etc.)
- SUBMISSION.md has all checkboxes ticked with in-repo paths
- Models used documented (e.g., Hermes: deepseek-v4-pro, OpenClaw: z-ai/glm-5.1)
**Effort:** XS

---

## Implementation Order (Dependencies Resolved)

### Phase 1: Foundation (Backend)
1. B-001: Laravel Project Setup
2. B-002: Database Migrations
3. B-003: Eloquent Models & Tenant Trait
4. B-004: Middleware
5. B-005: Policies

### Phase 2: Backend API
6. B-006: Form Requests
7. B-007: API Resources
8. B-008: Auth Controller
9. B-009: Ticket Controller
10. B-010: Comment Controller
11. B-011: User Controller
12. B-012: API Routes

### Phase 3: Backend Data & Tests
13. B-013: Demo Seeder
14. B-014: Feature Tests — Auth
15. B-015: Feature Tests — Tenant Isolation
16. B-016: Feature Tests — Tickets & Comments

### Phase 4: Frontend Foundation
17. F-001: Frontend Project Setup
18. F-002: API Client & Services
19. F-003: Auth Context & Hooks

### Phase 5: Frontend Pages
20. F-004: Public Pages (Login, Register)
21. F-005: Layout & Navigation
22. F-006: Dashboard Page
23. F-007: Ticket Detail Page
24. F-008: New & Edit Ticket Pages
25. F-009: Admin Page

### Phase 6: Polish & CI
26. F-010: Error Handling & Polish
27. F-011: App Routing
28. CI-001: GitHub Actions Workflow
29. DOC-001: Documentation & README

---

## Sprint Assignment

| Sprint | Tasks | Focus |
|---|---|---|
| Sprint 1 | B-001 to B-005 | Backend foundation, multi-tenancy, auth scaffolding |
| Sprint 2 | B-006 to B-012 | Backend API: tickets, comments, users, routes |
| Sprint 3 | B-013 to B-016 | Seeders, tests, tenant isolation validation |
| Sprint 4 | F-001 to F-005 | Frontend setup, auth, layout, services |
| Sprint 5 | F-006 to F-011, CI-001, DOC-001 | Dashboard, detail, forms, admin, routing, CI, docs |
