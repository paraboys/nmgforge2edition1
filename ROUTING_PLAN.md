# Routing Plan

## 1. Frontend Routing (React Router)
Defined in `App.jsx`.

### Public Routes
- `GET /login` -> `LoginPage` component.

### Protected Routes (Wrapped in `ProtectedLayout`)
- `GET /` -> `DashboardPage`
- `GET /tickets` -> `TicketListPage`
- `GET /tickets/:id` -> `TicketDetailPage`
- `GET /settings` -> `SettingsPage` (Admin only)

**Fallback:** `*` -> Redirect to `/` (or display a 404 Page).

## 2. Backend Routing (Laravel API)
Defined in `routes/api.php`.
Prefix: `/api`

### Public Routes
- `POST /login` -> `AuthController@login`

### Protected Routes (Middleware: `auth:sanctum`)
- `POST /logout` -> `AuthController@logout`
- `GET /user` -> `AuthController@user`
- `GET /users` -> `UserController@index` (Admin/Agent)

#### Tickets
- `GET /tickets` -> `TicketController@index`
- `POST /tickets` -> `TicketController@store`
- `GET /tickets/{ticket}` -> `TicketController@show`
- `PUT /tickets/{ticket}` -> `TicketController@update`

#### Conversations
- `GET /tickets/{ticket}/conversations` -> `ConversationController@index`
- `POST /tickets/{ticket}/conversations` -> `ConversationController@store`

#### Dashboard
- `GET /dashboard/metrics` -> `DashboardController@index`

#### Admin Settings
- `GET /tags` -> `TagController@index`
- `POST /tags` -> `TagController@store`
- `GET /sla-policies` -> `SlaPolicyController@index`
- `PUT /sla-policies/{sla_policy}` -> `SlaPolicyController@update`
