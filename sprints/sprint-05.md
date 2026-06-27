# Sprint 5: Quality, Polish & Delivery

## Goal
Finalize the build with robust seeders (for demo data), automated CI tests, and submission preparation.

## User Stories
- As a judge, I want to see pre-populated demo data so I can immediately interact with the app.
- As a reviewer, I want automated feature tests to prove the code works and respects tenant boundaries.
- As a reviewer, I want GitHub actions to run tests on every commit to prove CI/CD.
- As a reviewer, I want documentation (README) to easily run and understand the project.

## Technical Tasks
1. Create Laravel seeders: 1 Organization, 1 Admin, 2 Agents, 2 Customers.
2. Create `TicketSeeder` to generate ~12 tickets with varying statuses, priorities, assignees, and comments.
3. Write Pest/PHPUnit feature tests for:
   - `TicketController` (Authentication required, Multi-tenant isolation verified).
   - `AuthController` (Login returns token).
4. Implement GitHub Actions workflow (`.github/workflows/ci.yml`) to install dependencies, run migrations (sqlite memory), and execute tests.
5. Complete `README.md` with setup instructions and demo login details.
6. Verify `SUBMISSION.md` checklist is complete.

## Acceptance Criteria
- [ ] `php artisan migrate --seed` populates the database perfectly with a realistic demo state.
- [ ] Feature tests pass locally and prove tenant isolation.
- [ ] GitHub Actions workflow is green for the repository.
- [ ] README.md has clear instructions and demo credentials.
- [ ] SUBMISSION.md is filled out with exact file paths.

## Files Expected To Change
- `backend/database/seeders/DatabaseSeeder.php`
- `backend/tests/Feature/TicketTest.php`
- `backend/tests/Feature/TenantIsolationTest.php`
- `.github/workflows/ci.yml`
- `README.md`
- `SUBMISSION.md`

## Risks
- Seeders failing due to strict foreign key constraints. (Mitigation: Order seeders properly).
- GitHub actions failing on environment differences. (Mitigation: Use a standard Laravel CI yaml).

## Estimated Complexity
Medium - Focus is on rigor and presentation.
