# PulseDesk Architecture

## 1. Multi-tenant Design
PulseDesk uses a shared database, shared schema approach for multi-tenancy. Every tenant-specific table has an `organization_id` foreign key. We do not use separate databases or separate schemas per tenant to keep the operational overhead low while satisfying the SaaS requirements.

## 2. Organization Isolation Strategy
- All queries to tenant-aware tables must be scoped by the current user's `organization_id`.
- In Laravel, we will use Global Scopes on Eloquent Models (e.g., `TenantScope`) to automatically append `where('organization_id', auth()->user()->organization_id)` to every query.
- New records automatically inherit the `organization_id` of the authenticated user via Eloquent boot methods.

## 3. Authentication Flow
- **Framework:** Laravel Sanctum.
- **Method:** API Token or SPA Authentication (Cookie-based). Given we are separating React and Laravel (if on same domain, SPA auth is best; otherwise, standard token auth). We will use Sanctum API Tokens.
- **Flow:**
  1. User submits email/password to `/api/login`.
  2. Laravel validates credentials.
  3. Returns a Sanctum plain-text token.
  4. React frontend stores the token securely and attaches it as a `Bearer` token in the `Authorization` header for all subsequent API requests.

## 4. Authorization Strategy
- **Roles:** `admin`, `agent`, `customer`.
- **Implementation:** Laravel Policies and Gates.
- **Rules:**
  - `admin`: Full access to the organization's data, including settings, users, and all tickets.
  - `agent`: Access to view all organization tickets, reply to them, and manage their own assignments.
  - `customer`: Can only view and reply to tickets where they are the `requester_id`. Can only create tickets for themselves.

## 5. Database ER Design
*(See DATABASE_SCHEMA.md for full details)*
Core entities:
- **Organizations:** The tenant.
- **Users:** Belongs to an Organization. Has a Role.
- **Tickets:** Belongs to an Organization. Belongs to a Requester (User). Assigned to an Agent (User).
- **Conversations (Comments):** Belongs to a Ticket. Belongs to a User (Author). Can be public or internal note.
- **Tags/Activity Logs:** Associated with Tickets.

## 6. API Architecture
- **Paradigm:** RESTful JSON API.
- **Responses:** Standardized JSON payload with `data`, `meta`, and `links` (using Laravel Eloquent API Resources).
- **Versioning:** Unversioned for the hackathon MVP, prefixed with `/api/`.
- **Validation:** Laravel FormRequests.

## 7. Frontend Architecture
- **Framework:** React 19 + Vite.
- **Styling:** Tailwind CSS.
- **State Management:** React Context API for global state (Auth, Theme) + React Query (or swr) for server state management, caching, and data fetching.
- **Routing:** React Router v7.

## 8. Folder Structure
### Backend (Laravel)
```text
backend/
├── app/
│   ├── Http/Controllers/Api/
│   ├── Models/
│   ├── Policies/
│   ├── Scopes/             # For TenantScope
│   └── Providers/
├── routes/
│   └── api.php             # All REST endpoints
└── database/
    ├── migrations/
    └── seeders/
```
### Frontend (React)
```text
frontend/
├── src/
│   ├── components/         # Reusable UI (buttons, inputs, modals)
│   ├── pages/              # Route components (Dashboard, TicketList)
│   ├── hooks/              # Custom React hooks (useAuth, useTickets)
│   ├── services/           # Axios API clients
│   ├── context/            # AuthContext, etc.
│   └── utils/              # Helpers
└── index.css               # Tailwind directives
```

## 9. CI/CD Strategy
- **CI Pipeline:** GitHub Actions.
- **Triggers:** On Push or Pull Request to `main`.
- **Jobs:**
  1. **Checkout Code.**
  2. **Backend:** Setup PHP, install Composer dependencies, copy `.env.example`, generate key, run migrations (sqlite in memory for tests), run PHPUnit/Pest feature tests.
  3. **Frontend:** Setup Node, install NPM dependencies, run linter/build.
- **CD:** Manual deployment or automated via Forge/Envoyer to a VPS after successful CI.

## 10. Testing Strategy
- **Backend:** Pest or PHPUnit. Focus on Feature tests over Unit tests.
  - Test API endpoints for correct JSON structure.
  - Test multi-tenant isolation (User A cannot see User B's tickets).
  - Test authorization (Customer cannot delete a ticket).
- **Frontend:** Manual QA during the hackathon, plus strict TypeScript/ESLint rules.
