# Implementation Tasks (Task Breakdown)

This document divides the entire project into the smallest possible implementation tasks that can be completed independently by an AI coding agent.

## Task 1: Database Foundation & Multitenancy
- **Task ID:** BE-01
- **Objective:** Create the core tenant architecture and users.
- **Files to create:** `Organization` model/migration, `TenantScope`.
- **Files to modify:** `User` model, users migration.
- **Acceptance Criteria:** `php artisan migrate` runs successfully. `User` model automatically scopes to `organization_id`.
- **Dependencies:** None
- **Estimated effort:** Low

## Task 2: Backend Authentication
- **Task ID:** BE-02
- **Objective:** Implement Sanctum authentication.
- **Files to create:** `AuthController`, `LoginRequest`.
- **Files to modify:** `api.php` routes.
- **Acceptance Criteria:** `POST /api/login` returns a token and user details. Protected routes require the token.
- **Dependencies:** BE-01
- **Estimated effort:** Low

## Task 3: Tickets CRUD & Authorization
- **Task ID:** BE-03
- **Objective:** Build the core ticket engine.
- **Files to create:** `Ticket` model/migration, `TicketController`, `TicketPolicy`, `StoreTicketRequest`, `UpdateTicketRequest`, `TicketResource`.
- **Files to modify:** `api.php`.
- **Acceptance Criteria:** API supports GET, POST, PUT on tickets. Policies prevent customers from altering status/assignee or seeing other orgs.
- **Dependencies:** BE-02
- **Estimated effort:** High

## Task 4: Conversations
- **Task ID:** BE-04
- **Objective:** Add replies and internal notes to tickets.
- **Files to create:** `Conversation` model/migration, `ConversationController`, `StoreConversationRequest`, `ConversationResource`.
- **Files to modify:** `api.php`, `TicketResource` (to load conversations).
- **Acceptance Criteria:** Can post replies. Internal notes are hidden from customers.
- **Dependencies:** BE-03
- **Estimated effort:** Medium

## Task 5: Activity Logs & Observers
- **Task ID:** BE-05
- **Objective:** Automatically log changes to tickets.
- **Files to create:** `ActivityLog` model/migration, `TicketObserver`.
- **Files to modify:** `EventServiceProvider` (to register observer).
- **Acceptance Criteria:** Changing ticket status generates an `ActivityLog` row.
- **Dependencies:** BE-03
- **Estimated effort:** Low

## Task 6: Seeders & Testing
- **Task ID:** BE-06
- **Objective:** Prepare demo data and feature tests.
- **Files to create:** `DatabaseSeeder`, `TicketSeeder`, `TenantIsolationTest.php`.
- **Files to modify:** None.
- **Acceptance Criteria:** `php artisan db:seed` provisions a full test environment. Tests pass.
- **Dependencies:** BE-05
- **Estimated effort:** Medium

## Task 7: Frontend Setup & Auth
- **Task ID:** FE-01
- **Objective:** Initialize React app routing and authentication context.
- **Files to create:** `AuthContext.jsx`, `api.js` (axios), `LoginPage.jsx`, `ProtectedLayout.jsx`, `App.jsx` (router setup).
- **Files to modify:** `main.jsx`, `index.css`.
- **Acceptance Criteria:** User can login via UI and token is saved in Context/localStorage.
- **Dependencies:** BE-02
- **Estimated effort:** Medium

## Task 8: Dashboard & Navigation
- **Task ID:** FE-02
- **Objective:** Build the main shell of the app.
- **Files to create:** `Sidebar.jsx`, `Header.jsx`, `DashboardPage.jsx`, `MetricCard.jsx`.
- **Files to modify:** `ProtectedLayout.jsx`.
- **Acceptance Criteria:** Authenticated users see the sidebar and a static or dynamic dashboard.
- **Dependencies:** FE-01
- **Estimated effort:** Low

## Task 9: Ticket List
- **Task ID:** FE-03
- **Objective:** Display and filter the ticket queue.
- **Files to create:** `TicketListPage.jsx`, `TicketTable.jsx`, `TicketFilterBar.jsx`.
- **Files to modify:** `App.jsx`.
- **Acceptance Criteria:** Table fetches data from `/api/tickets` and renders it. Filters trigger refetches.
- **Dependencies:** FE-02, BE-03
- **Estimated effort:** High

## Task 10: Ticket Detail & Replies
- **Task ID:** FE-04
- **Objective:** Implement full ticket view and conversation threading.
- **Files to create:** `TicketDetailPage.jsx`, `ConversationList.jsx`, `ReplyBox.jsx`.
- **Files to modify:** `App.jsx`.
- **Acceptance Criteria:** User can read a ticket, see replies, post a new reply, and (if agent) toggle an internal note.
- **Dependencies:** FE-03, BE-04
- **Estimated effort:** High
