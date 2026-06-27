# Sprint 2: Ticket Engine

## Goal
Allow users to create, view, and manage support tickets with basic metadata.

## User Stories
- As a customer, I want to create a new support ticket so I can get help.
- As an agent, I want to see a list of tickets in my organization.
- As an agent, I want to assign a ticket to myself or a colleague.
- As an agent, I want to change a ticket's status and priority.
- As an agent, I want to add tags to a ticket to categorize it.

## Technical Tasks
1. Create `tickets`, `tags`, and `tag_ticket` table migrations.
2. Build `Ticket`, `Tag` Models with relationships and `TenantScope`.
3. Implement `GET /api/tickets`, `POST /api/tickets`, `GET /api/tickets/{id}`, `PUT /api/tickets/{id}`.
4. Add authorization checks (Policies) so customers can only see/update their own tickets.
5. Create React `TicketListPage` and `TicketDetailPage` scaffolding.
6. Build `CreateTicketModal` in React.

## Acceptance Criteria
- [ ] Customers can create tickets; Agents can view all org tickets.
- [ ] Ticket list endpoint supports basic filtering (by status/priority).
- [ ] Agents can update assignee, status, and priority via the API and UI.
- [ ] Tagging functionality is supported on tickets.

## Files Expected To Change
- `backend/database/migrations/..._create_tickets_table.php`
- `backend/database/migrations/..._create_tags_table.php`
- `backend/app/Models/Ticket.php`
- `backend/app/Http/Controllers/Api/TicketController.php`
- `backend/app/Policies/TicketPolicy.php`
- `frontend/src/pages/TicketListPage.jsx`
- `frontend/src/pages/TicketDetailPage.jsx`
- `frontend/src/components/CreateTicketModal.jsx`

## Risks
- Incorrect authorization logic exposing one customer's tickets to another customer. (Mitigation: Strict Policy checks and testing).

## Estimated Complexity
High - This is the core functionality of the SaaS.
