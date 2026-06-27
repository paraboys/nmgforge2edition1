# PulseDesk — Routing Plan

> React Router DOM v7 (declarative, nested routes)
> All routes use `BrowserRouter` with `basename` = `/` (deployed root)

---

## Route Table

| Route | Path | Component | Auth Required | Role Restriction | Layout |
|---|---|---|---|---|---|
| Login | `/login` | `LoginPage` | No | None | None (full page) |
| Register | `/register` | `RegisterPage` | No | None | None (full page) |
| Dashboard | `/` | `DashboardPage` | Yes | None | `Layout` |
| Dashboard (alt) | `/dashboard` | `DashboardPage` | Yes | None | `Layout` |
| Ticket Detail | `/tickets/:id` | `TicketDetailPage` | Yes | None | `Layout` |
| New Ticket | `/tickets/new` | `NewTicketPage` | Yes | None | `Layout` |
| Edit Ticket | `/tickets/:id/edit` | `EditTicketPage` | Yes | Admin/Agent | `Layout` |
| Admin | `/admin` | `AdminPage` | Yes | Admin only | `Layout` |
| Not Found | `*` | `NotFoundPage` | No | None | `Layout` (or minimal) |

---

## Route Configuration (App.jsx)

```jsx
import { Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { DashboardPage } from './pages/DashboardPage';
import { TicketDetailPage } from './pages/TicketDetailPage';
import { NewTicketPage } from './pages/NewTicketPage';
import { EditTicketPage } from './pages/EditTicketPage';
import { AdminPage } from './pages/AdminPage';
import { NotFoundPage } from './pages/NotFoundPage';
import { AuthGuard } from './components/AuthGuard';
import { RoleGuard } from './components/RoleGuard';

function App() {
  return (
    <AuthProvider>
      <Routes>
        {/* Public routes */}
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        
        {/* Protected routes */}
        <Route element={<AuthGuard />}>
          <Route path="/" element={<DashboardPage />} />
          <Route path="/dashboard" element={<DashboardPage />} />
          <Route path="/tickets/new" element={<NewTicketPage />} />
          <Route path="/tickets/:id" element={<TicketDetailPage />} />
          
          {/* Admin/Agent only */}
          <Route element={<RoleGuard allowedRoles={['admin', 'agent']} />}>
            <Route path="/tickets/:id/edit" element={<EditTicketPage />} />
          </Route>
          
          {/* Admin only */}
          <Route element={<RoleGuard allowedRoles={['admin']} />}>
            <Route path="/admin" element={<AdminPage />} />
          </Route>
        </Route>
        
        {/* Catch-all */}
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </AuthProvider>
  );
}
```

---

## Guard Components

### AuthGuard
- **Behavior**: if `!user` -> render `<Navigate to="/login" replace />`
- **Loading**: if `loading` -> render `<LoadingSpinner fullPage/>`
- **Outlet**: renders `<Outlet />` for nested routes (use `useOutlet` from react-router-dom v7)

### RoleGuard
- **Props**: `allowedRoles` array of strings
- **Behavior**: if `user.role` not in `allowedRoles` -> render `<Navigate to="/dashboard" replace />`
- **Nested**: wraps routes inside `AuthGuard`; assumes `user` is present

---

## Navigation Patterns

### Sidebar Navigation
- `/dashboard` — "Dashboard" (all roles)
- `/tickets/new` — "New Ticket" (all roles)
- `/admin` — "Admin" (admin only, hidden for others)

### Programmatic Navigation
- After login/register: `navigate('/dashboard')`
- After ticket create: `navigate(\`/tickets/${id}\`)`
- After ticket update: `navigate(\`/tickets/${id}\`)`
- After ticket delete: `navigate('/dashboard')`
- Unauthorized role: `navigate('/dashboard')`
- 404: stay on `NotFoundPage` with "Go to Dashboard" link

### URL Parameters
- `:id` — ticket ID (integer, validated in component via `useParams`)
- `?status=open&priority=high&page=2` — query params for filters, read via `useSearchParams`

---

## Query Param Synchronization (Dashboard Filters)

- On filter change: `setSearchParams({ status: 'open', priority: 'high' })`
- On mount: read `searchParams` from URL, populate filter state
- This allows bookmarkable/shareable filtered views
- Empty params omitted (clean URLs)

---

## Deep Linking

- `/tickets/42` — direct link to ticket 42
- `/tickets/42/edit` — direct link to edit ticket 42 (admin/agent only)
- `/dashboard?status=open&assignee=3` — bookmarkable filtered view

---

## Route Loading Strategy

- **No lazy loading** for MVP (all components imported statically)
- **Data fetching**: triggered inside page components via `useEffect` and custom hooks
- **Skeleton loading**: show `LoadingSpinner` or `TicketCard` skeletons while fetching
- **Error handling**: if fetch fails, show error UI with retry button (do not redirect)

---

## 404 Not Found Page

- **Minimal layout**: centered card with icon + "Page not found" + "Go to Dashboard" link
- **No sidebar**: clean, uncluttered
- **Accessible**: `h1` with proper heading, `role="alert"`