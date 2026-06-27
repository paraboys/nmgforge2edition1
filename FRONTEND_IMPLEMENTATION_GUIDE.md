# Frontend Implementation Guide

## Implementation Order
1. Routing Setup (React Router) & Tailwind config.
2. Auth Context & API Service (Axios interceptors).
3. Public Pages (Login).
4. Layout Components (Sidebar, Header).
5. Protected Routes & Dashboard.
6. Ticket List & Filters.
7. Ticket Detail & Conversations.
8. Admin Settings (Users, Tags, SLAs).

## 1. Folder Structure (React Focus)
```text
frontend/src/
├── components/
│   ├── common/
│   │   ├── Button.jsx
│   │   ├── Input.jsx
│   │   ├── Select.jsx
│   │   ├── Badge.jsx
│   │   └── Modal.jsx
│   ├── layout/
│   │   ├── ProtectedLayout.jsx
│   │   ├── Sidebar.jsx
│   │   └── Header.jsx
│   └── tickets/
│       ├── TicketFilterBar.jsx
│       ├── TicketTable.jsx
│       ├── CreateTicketModal.jsx
│       ├── ConversationList.jsx
│       └── ReplyBox.jsx
├── context/
│   └── AuthContext.jsx
├── pages/
│   ├── LoginPage.jsx
│   ├── DashboardPage.jsx
│   ├── TicketListPage.jsx
│   ├── TicketDetailPage.jsx
│   └── SettingsPage.jsx          # Admin setting management
├── services/
│   └── api.js                  # Axios instance with interceptors
└── App.jsx                     # Router definitions
```

## 2. API Integration & Axios
- Use `axios.create()`.
- Add an interceptor to automatically attach `Authorization: Bearer {token}` from localStorage.
- Add an interceptor to handle `401 Unauthorized` responses by clearing localStorage and redirecting to `/login`.

## 3. AuthContext (State Management)
- Keep `user` object and `token` in context state.
- `login(email, password)`: calls `/api/login`, saves token, sets user.
- `logout()`: calls `/api/logout`, clears token, unsets user.

## 4. Protected Routes
- `ProtectedLayout.jsx`: Checks if `user` exists in `AuthContext`. If not, renders `<Navigate to="/login" />`.
- Wraps all routes except `/login`. Admin-only routes must check `user.role === 'admin'`.

## 5. UI Elements & Forms
- **TicketTable**: Maps over data array. Displays `Badge` for status (e.g., green for resolved, gray for closed, red for open).
- **Forms (CreateTicketModal)**: Use controlled inputs (useState). Show loading spinner on submit button. Display error messages if API returns 422.
- **ReplyBox**: Contains a `<textarea>`. If `user.role !== 'customer'`, show a toggle/checkbox for "Internal Note". Changes background color slightly if internal note is selected to warn the agent.

## 6. Dashboard Widgets
- **Metric Cards**: Simple flex containers displaying numbers (Total Open, My Assigned).
- Fetch from `/api/dashboard/metrics`.

## 7. Search and Filters
- Store filter state in URL search parameters or `useState`.
- When filter changes, trigger API refetch (e.g., `/api/tickets?status=open&search=login`).
- `TicketFilterBar` contains a text input (debounced) and dropdowns for Status and Priority.

## 8. Settings Management (Admin)
- Provide a `SettingsPage` with tabs:
  - Users: View system users.
  - Tags: Create/Edit tags.
  - SLAs: Configure response/resolution timers per priority.

## 9. Error Handling & Loading States
- Wrap API calls in `try/catch`. Display generic error toasts or inline red text.
- Use `isLoading` booleans to render skeleton loaders or spinners inside tables and buttons during data fetching.
