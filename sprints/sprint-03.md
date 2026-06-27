# Sprint 3: Communication Layer

## Goal
Enable back-and-forth communication on tickets, separating public customer replies from internal agent notes.

## User Stories
- As a customer, I want to reply to a ticket to provide more information.
- As an agent, I want to reply to a customer to solve their issue.
- As an agent, I want to leave an internal note that the customer cannot see.
- As a user, I want to upload attachments to a ticket/reply.

## Technical Tasks
1. Create `conversations` table migration and Model.
2. Add `is_internal` boolean flag to `conversations`.
3. Implement `GET /api/tickets/{id}/conversations` and `POST /api/tickets/{id}/conversations`.
4. Ensure `is_internal` replies are hidden from customers at the API level (Policy/Scope).
5. Update React `TicketDetailPage` to list conversations chronologically.
6. Build a `ReplyBox` React component with a toggle for "Public Reply" vs "Internal Note" (Agent only).
7. Architect attachments: define storage path (local vs S3) and database schema (polymorphic `media` table or simple column on conversations). *Implementation of upload can be deferred if out of time.*

## Acceptance Criteria
- [ ] Customers and Agents can add conversations to a ticket.
- [ ] Agents can mark a conversation as internal.
- [ ] Customers never receive internal conversations in the API response.
- [ ] UI visually distinguishes between customer replies, agent replies, and internal notes.

## Files Expected To Change
- `backend/database/migrations/..._create_conversations_table.php`
- `backend/app/Models/Conversation.php`
- `backend/app/Http/Controllers/Api/ConversationController.php`
- `frontend/src/pages/TicketDetailPage.jsx`
- `frontend/src/components/ConversationList.jsx`
- `frontend/src/components/ReplyBox.jsx`

## Risks
- Data leak: Customer viewing internal notes. Must be tested thoroughly.

## Estimated Complexity
Medium - Mainly CRUD but with strict visibility rules.
