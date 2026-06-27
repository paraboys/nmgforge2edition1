# Backend Implementation Guide

## Implementation Order
1. Setup global `TenantScope` and Migrations.
2. Models and Relationships.
3. AuthController & Policies (Security Layer).
4. TicketController & ConversationController (Core Logic).
5. DashboardController.
6. Observers (Activity Logs).
7. Seeders.
8. Testing.

## 1. Laravel Folder Structure Focus
```text
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── TicketController.php
│   │   │   ├── ConversationController.php
│   │   │   └── DashboardController.php
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php
│   │   │   ├── StoreTicketRequest.php
│   │   │   ├── UpdateTicketRequest.php
│   │   │   └── StoreConversationRequest.php
│   │   └── Resources/
│   │       ├── TicketResource.php
│   │       ├── ConversationResource.php
│   │       └── UserResource.php
│   ├── Models/
│   │   ├── Organization.php, User.php, Ticket.php, Conversation.php
│   │   ├── Tag.php, ActivityLog.php
│   ├── Observers/
│   │   └── TicketObserver.php
│   ├── Policies/
│   │   ├── TicketPolicy.php
│   │   └── ConversationPolicy.php
│   └── Scopes/
│       └── TenantScope.php
└── database/seeders/
    └── DatabaseSeeder.php
```

## 2. Global Scopes & Multitenancy
- **`TenantScope.php`**: Applies `->where('organization_id', auth()->user()->organization_id)` to queries.
- **Boot Methods**: In `User`, `Ticket`, `Conversation`, `Tag`, `ActivityLog` override the `booted` method to add the global scope. Also use `creating` event to automatically set `$model->organization_id = auth()->user()->organization_id`.

## 3. Models & Relationships
- **User**: `belongsTo` Organization, `hasMany` Tickets (requester), `hasMany` AssignedTickets (assignee).
- **Ticket**: `belongsTo` User (requester, assignee), `hasMany` Conversations, `belongsToMany` Tags, `hasMany` ActivityLogs.
- **Conversation**: `belongsTo` Ticket, `belongsTo` User (author).

## 4. API Resources
- `TicketResource`: Includes requester and assignee names, status, priority, and conditionally includes `conversations` (if loaded).
- `ConversationResource`: Filters out `is_internal = true` if `auth()->user()->role === 'customer'`.

## 5. Controllers & Logic
- **`TicketController@index`**: Uses Eloquent query builder.
  - Apply filters: `when($request->status, fn($q) => $q->where('status', $request->status))`
  - Handle search: `when($request->search, fn($q) => $q->where('subject', 'like', "%{$request->search}%"))`
  - Paginate results: `return TicketResource::collection($query->paginate(15));`
- **`TicketController@store`**: Uses `StoreTicketRequest`. Set `requester_id` to current user (unless admin creating for someone else).

## 6. Authorization Rules (Policies)
- **`TicketPolicy`**:
  - `viewAny`: true (Scope handles filtering).
  - `view`: `admin/agent` -> true. `customer` -> `$user->id === $ticket->requester_id`.
  - `update`: `admin/agent` -> true. `customer` -> false (can't update status).
- **`ConversationPolicy`**:
  - `create`: Same as view ticket.

## 7. Observers (Activity Log)
- **`TicketObserver@updated`**: Check `$ticket->isDirty('status')` or `isDirty('assignee_id')`. If so, create `ActivityLog`.

## 8. Error Handling
- Let Laravel handle `ModelNotFoundException` (returns 404).
- Use FormRequests to automatically return 422 JSON on validation failure.
- Ensure API middleware uses `Accept: application/json` to prevent redirects.

## 9. Testing Requirements
- Use `Pest` or `PHPUnit`.
- Create `TenantIsolationTest`: Create 2 organizations. User in Org A fetches `/api/tickets`. Assert tickets from Org B are NOT returned.
- Create `AuthorizationTest`: Customer attempts to update ticket status via `PUT /api/tickets/{ticket}`. Assert 403 Forbidden.
