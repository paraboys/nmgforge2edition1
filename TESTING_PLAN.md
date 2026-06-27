# Testing Plan

## 1. Backend Testing Strategy (Laravel Pest/PHPUnit)

We will focus entirely on **Feature Tests** as they provide the highest confidence that the API is functioning correctly end-to-end. Unit tests are unnecessary for simple CRUD logic.

### 1.1 Tenant Isolation Tests
**Objective:** Ensure Organization A cannot view or modify Organization B's data.
- **Test:** Create Org A and Org B. Create Ticket in Org B. User from Org A calls `GET /api/tickets`. Assert Ticket from Org B is not in response.
- **Test:** User from Org A calls `GET /api/tickets/{org_b_ticket_id}`. Assert 403 Forbidden or 404 Not Found.

### 1.2 Authentication Tests
- **Test:** `POST /api/login` with valid credentials returns 200 and token.
- **Test:** `POST /api/login` with invalid credentials returns 401.

### 1.3 Ticket & SLA Authorization Tests
- **Test:** Customer can create a ticket.
- **Test:** Customer cannot update ticket status (assert 403).
- **Test:** Agent can update ticket status.
- **Test:** Customer cannot view an internal note.
- **Test:** Agent can view an internal note.
- **Test:** Agent cannot update SLA policy (Admin only, assert 403).

## 2. Frontend Testing Strategy
Due to the rapid nature of the hackathon, automated frontend testing is a stretch goal.
- **Manual QA Protocol:**
  - Login as Admin. Verify dashboard loads.
  - Navigate to Settings. Create a Tag. Update SLA policy.
  - Create a ticket. Check if it appears in the list.
  - Change ticket status and priority.
  - Add an internal note.
  - Logout.
  - Login as Customer. Verify customer only sees their own ticket. Verify customer does NOT see the internal note.
  - Add a public reply as Customer.
  - Verify activity log correctly recorded the changes.

## 3. GitHub Actions CI
- Triggered on push to `main` and pull requests.
- Steps:
  1. `actions/checkout`
  2. Setup PHP 8.2 & Node.js
  3. **Backend Build:** `composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist`
  4. Copy `.env.example` to `.env` and `php artisan key:generate`
  5. **Backend Tests:** `php artisan migrate` (using SQLite in-memory) and `php artisan test`
  6. **Frontend Build:** `cd frontend && npm install && npm run build` (Ensures Vite builds successfully)
- Pipeline must pass before merging PRs.
