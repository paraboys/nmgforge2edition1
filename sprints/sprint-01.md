# Sprint 1: Core Foundation & Auth

## Goal
Establish the secure, tenant-isolated foundation so that users can log in and see only their organization's data.

## User Stories
- As a system administrator, I want organizations (tenants) so that data can be partitioned.
- As a user (agent/customer/admin), I want to log in using an email and password so I can securely access the platform.
- As an API consumer, I need a Sanctum token returned upon login to authenticate subsequent requests.
- As a tenant user, I must never see data belonging to another organization.

## Technical Tasks
1. Verify Laravel and React starter setup.
2. Create `organizations` table migration and Model.
3. Update `users` table to include `organization_id` and `role`.
4. Create a global `TenantScope` for Eloquent to automatically filter queries by `organization_id`.
5. Implement `POST /api/login` using Laravel Sanctum to return a token.
6. Build React `LoginPage` and `AuthContext` to store the token.
7. Setup React Router for protected routes.

## Acceptance Criteria
- [ ] Database contains `organizations` and updated `users` tables.
- [ ] `POST /api/login` successfully authenticates a user and returns a Sanctum token.
- [ ] React frontend has a working login screen that redirects to a protected dashboard upon success.
- [ ] Any model with the `TenantScope` trait automatically appends `WHERE organization_id = ?` to queries.

## Files Expected To Change
- `backend/database/migrations/..._create_organizations_table.php`
- `backend/database/migrations/..._update_users_table.php`
- `backend/app/Models/User.php`
- `backend/app/Models/Organization.php`
- `backend/app/Scopes/TenantScope.php`
- `backend/app/Http/Controllers/Api/AuthController.php`
- `frontend/src/context/AuthContext.jsx`
- `frontend/src/pages/LoginPage.jsx`

## Risks
- Misconfiguring Sanctum with SPA vs API token auth. (Decision: We will stick to plain-text tokens returned via JSON for simplicity in decoupling).
- Global Scope breaking console commands or seeder. (Need to ensure `withoutGlobalScopes()` is used in seeders).

## Estimated Complexity
Medium - Crucial foundation layer. Must be exact.
