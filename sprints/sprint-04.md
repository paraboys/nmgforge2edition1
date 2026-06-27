# Sprint 4: Insights & Management

## Goal
Provide visibility into the ticket queue, allow easy finding of tickets, and track the audit trail of actions.

## User Stories
- As an agent, I want to search for tickets by subject or description.
- As an agent, I want to filter the ticket list by status and priority.
- As an admin, I want to see a dashboard with metrics (open tickets, avg response time) to understand the team's workload.
- As an admin/agent, I want to see an activity log on a ticket (e.g., "Status changed from Open to Resolved").

## Technical Tasks
1. Enhance `GET /api/tickets` to accept `search`, `status`, and `priority` query parameters and filter the Eloquent query.
2. Build `GET /api/dashboard/metrics` endpoint to aggregate data (count by status, etc.).
3. Create `activity_logs` table migration and Model.
4. Hook into Eloquent Model Events (or Observers) on the `Ticket` model to automatically create an `ActivityLog` when status, priority, or assignee changes.
5. Create React `DashboardPage` with basic Metric cards.
6. Enhance `TicketListPage` UI with a robust `FilterBar`.
7. Add an `ActivityLogList` component to the `TicketDetailPage`.

## Acceptance Criteria
- [ ] Text search on tickets works and is performant.
- [ ] Dashboard shows accurate aggregate numbers for the tenant.
- [ ] Ticket history (Activity Log) accurately records changes automatically.

## Files Expected To Change
- `backend/app/Http/Controllers/Api/TicketController.php`
- `backend/app/Http/Controllers/Api/DashboardController.php`
- `backend/database/migrations/..._create_activity_logs_table.php`
- `backend/app/Models/ActivityLog.php`
- `backend/app/Observers/TicketObserver.php`
- `frontend/src/pages/DashboardPage.jsx`
- `frontend/src/pages/TicketListPage.jsx`
- `frontend/src/components/ActivityLogList.jsx`

## Risks
- Observers triggering infinite loops if not careful. (Mitigation: Ensure observers don't call `save()` redundantly).
- Performance of text search on large tables. (Mitigation: Add DB indexes, keep it simple for MVP).

## Estimated Complexity
Medium - Adds depth and polish.
