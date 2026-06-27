# PulseDesk — Frontend Implementation Guide

> Stack: React 19 · Vite · Tailwind CSS · Axios · React Router DOM v7

---

## 1. Project Setup (Task F-001)

### Files to create/modify
- `frontend/package.json` — dependencies: `react`, `react-dom`, `react-router-dom`, `axios`, `tailwindcss`, `autoprefixer`, `postcss`, `@tailwindcss/forms`
- `frontend/vite.config.js` — Vite + React plugin + proxy to backend
- `frontend/tailwind.config.js` — content paths, custom colors
- `frontend/index.html` — root HTML, mount `src/main.jsx`
- `frontend/src/main.jsx` — React root, `BrowserRouter`, `AuthProvider`
- `frontend/.env.example` — `VITE_API_URL=http://127.0.0.1:8000`

### Vite config proxy (for dev)
```js
server: {
  proxy: {
    '/api': { target: 'http://127.0.0.1:8000', changeOrigin: true }
  }
}
```

### Tailwind config
```js
module.exports = {
  content: ['./index.html', './src/**/*.{js,jsx,ts,tsx}'],
  theme: {
    extend: {
      colors: {
        primary: { 50: '#eff6ff', 500: '#3b82f6', 700: '#1d4ed8' },
        status: {
          open: '#ef4444',
          pending: '#f59e0b',
          resolved: '#22c55e',
          closed: '#6b7280',
        },
        priority: {
          low: '#6b7280',
          medium: '#3b82f6',
          high: '#f59e0b',
          urgent: '#ef4444',
        }
      }
    }
  },
  plugins: [require('@tailwindcss/forms')],
}
```

---

## 2. API Layer (Task F-002)

### `frontend/src/api/api.js`
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { 'Content-Type': 'application/json' },
});

// Request interceptor: attach Bearer token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Response interceptor: 401 -> logout + redirect
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### `frontend/src/services/authService.js`
```javascript
import api from '../api/api';

export const authService = {
  register: (data) => api.post('/register', data),
  login: (data) => api.post('/login', data),
  logout: () => api.post('/logout'),
  me: () => api.get('/me'),
};
```

### `frontend/src/services/ticketService.js`
```javascript
import api from '../api/api';

export const ticketService = {
  list: (params) => api.get('/tickets', { params }),
  get: (id) => api.get(`/tickets/${id}`),
  create: (data) => api.post('/tickets', data),
  update: (id, data) => api.put(`/tickets/${id}`, data),
  delete: (id) => api.delete(`/tickets/${id}`),
  getComments: (ticketId) => api.get(`/tickets/${ticketId}/comments`),
  addComment: (ticketId, data) => api.post(`/tickets/${ticketId}/comments`, data),
};
```

### `frontend/src/services/commentService.js`
```javascript
import api from '../api/api';

export const commentService = {
  list: (ticketId) => api.get(`/tickets/${ticketId}/comments`),
  create: (ticketId, data) => api.post(`/tickets/${ticketId}/comments`, data),
};
```

### `frontend/src/services/userService.js`
```javascript
import api from '../api/api';

export const userService = {
  list: (params) => api.get('/users', { params }),
  create: (data) => api.post('/users', data),
  get: (id) => api.get(`/users/${id}`),
};
```

---

## 3. Auth Context & Hooks (Task F-003)

### `frontend/src/context/AuthContext.jsx`
```jsx
import { createContext, useContext, useState, useEffect } from 'react';
import { authService } from '../services/authService';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(() => {
    const saved = localStorage.getItem('user');
    return saved ? JSON.parse(saved) : null;
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token && !user) {
      authService.me()
        .then(res => {
          setUser(res.data.data);
          localStorage.setItem('user', JSON.stringify(res.data.data));
        })
        .catch(() => logout())
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (credentials) => {
    const res = await authService.login(credentials);
    const { user: u, token } = res.data.data;
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(u));
    setUser(u);
    return u;
  };

  const register = async (data) => {
    const res = await authService.register(data);
    const { user: u, token } = res.data.data;
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(u));
    setUser(u);
    return u;
  };

  const logout = async () => {
    try { await authService.logout(); } catch (e) {}
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
  };

  const isAdmin = () => user?.role === 'admin';
  const isAgent = () => user?.role === 'agent';
  const isCustomer = () => user?.role === 'customer';

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout, isAdmin, isAgent, isCustomer }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
```

### `frontend/src/hooks/useAuth.js`
```javascript
import { useAuth as useAuthContext } from '../context/AuthContext';
export const useAuth = () => useAuthContext();
```

### `frontend/src/hooks/useTickets.js`
```javascript
import { useState, useEffect, useCallback } from 'react';
import { ticketService } from '../services/ticketService';

export const useTickets = (filters = {}) => {
  const [tickets, setTickets] = useState([]);
  const [meta, setMeta] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetch = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const res = await ticketService.list({ ...filters, ...params });
      setTickets(res.data.data);
      setMeta(res.data.meta || {});
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load tickets');
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => { fetch(); }, [fetch]);

  return { tickets, meta, loading, error, refetch: fetch };
};
```

### `frontend/src/hooks/useTicket.js`
```javascript
import { useState, useEffect } from 'react';
import { ticketService } from '../services/ticketService';

export const useTicket = (id) => {
  const [ticket, setTicket] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!id) return;
    setLoading(true);
    ticketService.get(id)
      .then(res => setTicket(res.data.data))
      .catch(err => setError(err.response?.data?.message || 'Failed to load ticket'))
      .finally(() => setLoading(false));
  }, [id]);

  return { ticket, loading, error, setTicket };
};
```

### `frontend/src/hooks/useComments.js`
```javascript
import { useState, useEffect } from 'react';
import { commentService } from '../services/commentService';

export const useComments = (ticketId) => {
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(false);

  const fetch = () => {
    if (!ticketId) return;
    setLoading(true);
    commentService.list(ticketId)
      .then(res => setComments(res.data.data))
      .finally(() => setLoading(false));
  };

  useEffect(() => { fetch(); }, [ticketId]);

  const add = async (data) => {
    const res = await commentService.create(ticketId, data);
    setComments(prev => [...prev, res.data.data]);
  };

  return { comments, loading, refetch: fetch, addComment: add };
};
```

### `frontend/src/hooks/useLocalStorage.js`
```javascript
import { useState, useEffect } from 'react';

export const useLocalStorage = (key, initialValue) => {
  const [value, setValue] = useState(() => {
    try { return JSON.parse(localStorage.getItem(key)) || initialValue; }
    catch { return initialValue; }
  });
  useEffect(() => { localStorage.setItem(key, JSON.stringify(value)); }, [key, value]);
  return [value, setValue];
};
```

---

## 4. Pages (Task F-004)

### `frontend/src/pages/LoginPage.jsx`
- **Layout**: centered card, dark background, logo
- **Form fields**: email, password, submit button
- **Actions**: call `login()`, store token, redirect to `/dashboard`
- **Error**: display error message from API
- **Link**: "Don't have an account? Register"
- **Unauthenticated only**: redirect to `/dashboard` if already logged in

### `frontend/src/pages/RegisterPage.jsx`
- **Form fields**: organization name, full name, email, password, password confirmation
- **Validation**: client-side before submit (required, email format, password min 8, match)
- **Actions**: call `register()`, store token, redirect to `/dashboard`
- **Link**: "Already have an account? Login"

### `frontend/src/pages/DashboardPage.jsx`
- **Layout**: `Layout` with sidebar + main content
- **Components**: `FilterBar`, `SearchBar`, `TicketList`
- **State**: filters (`status`, `priority`, `assignee`, `search`), pagination
- **Data**: `useTickets(filters)`
- **Actions**: clicking ticket navigates to `/tickets/:id`
- **Create button**: visible to all roles, navigates to `/tickets/new`
- **Role-based**: admin/agent see all org tickets; customer sees own tickets only (backend enforces)

### `frontend/src/pages/TicketDetailPage.jsx`
- **Layout**: `Layout`
- **Components**: ticket info card, `StatusBadge`, `PriorityBadge`, `CommentThread`, `CommentForm`
- **Data**: `useTicket(id)`, `useComments(id)`
- **Actions**: 
  - admin/agent: change status, change priority, assign/unassign, add internal note
  - customer: add public reply only
- **Internal notes**: hidden from customer role; visible badge for agents
- **Back button**: returns to `/dashboard`

### `frontend/src/pages/NewTicketPage.jsx`
- **Form fields**: subject, description, priority (dropdown), tags (comma-separated input)
- **Validation**: subject required, max 255; description required; priority required
- **Actions**: submit -> `ticketService.create()` -> redirect to `/tickets/:id`
- **Cancel**: back to `/dashboard`

### `frontend/src/pages/EditTicketPage.jsx`
- **Pre-fill**: load existing ticket data
- **Fields**: same as NewTicketPage + status dropdown (admin/agent only)
- **Actions**: `ticketService.update()` -> redirect to detail page
- **Delete button**: admin only, confirmation dialog, then `ticketService.delete()` -> redirect to dashboard

### `frontend/src/pages/AdminPage.jsx` (Should-tier — Sprint 4)
- **Layout**: `Layout`
- **Components**: user list table, invite/create user form
- **Admin-only**: redirect non-admin to `/dashboard`
- **Actions**: create agent/customer users, view org stats

---

## 5. Components (Task F-005)

### `frontend/src/components/AuthGuard.jsx`
```jsx
import { Navigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';

export const AuthGuard = ({ children }) => {
  const { user, loading } = useAuth();
  if (loading) return <LoadingSpinner />;
  if (!user) return <Navigate to="/login" replace />;
  return children;
};
```

### `frontend/src/components/Layout.jsx`
```jsx
export const Layout = ({ children }) => {
  return (
    <div className="min-h-screen bg-gray-50">
      <Navbar />
      <div className="flex">
        <Sidebar />
        <main className="flex-1 p-6">{children}</main>
      </div>
    </div>
  );
};
```

### `frontend/src/components/Navbar.jsx`
- **Content**: app logo/link, user name + role badge, logout button
- **Actions**: logout -> `auth.logout()` -> redirect `/login`

### `frontend/src/components/Sidebar.jsx`
- **Links**: Dashboard, New Ticket, Admin (admin only)
- **Active state**: highlight current route
- **Collapsible**: mobile responsive (hamburger menu)

### `frontend/src/components/TicketCard.jsx`
- **Props**: `ticket` object
- **Display**: subject, status badge, priority badge, requester name, assignee name (or "Unassigned"), created date
- **Action**: click navigates to `/tickets/:id`
- **Hover**: shadow lift, cursor pointer

### `frontend/src/components/TicketList.jsx`
- **Props**: `tickets` array, `loading` boolean, `meta` object
- **Display**: grid/list of `TicketCard` components
- **Empty state**: `EmptyState` component with icon + message
- **Pagination**: simple prev/next buttons using `meta.current_page`, `meta.last_page`

### `frontend/src/components/TicketForm.jsx`
- **Props**: `initialData` (optional), `onSubmit`, `onCancel`, `isEdit` (boolean)
- **Fields**: subject (input), description (textarea), priority (select), tags (input), status (select, admin/agent only if edit)
- **Validation**: real-time on blur, submit blocked if invalid
- **State**: controlled form with `useState`
- **Loading**: submit button disabled + spinner during submission

### `frontend/src/components/CommentThread.jsx`
- **Props**: `comments` array, `currentUser` object
- **Display**: chronological list of comments
- **Internal note styling**: different background color (yellow/amber tint), "Internal" badge
- **Author**: avatar + name + role badge + timestamp
- **Customer view**: internal notes are filtered out (backend handles this, but frontend can double-check `is_internal`)

### `frontend/src/components/CommentForm.jsx`
- **Props**: `onSubmit`, `isInternal` option (admin/agent only)
- **Fields**: textarea for body, checkbox for "Internal note" (role-gated)
- **Submit**: Enter key (Ctrl+Enter) or button
- **Validation**: body required, non-empty

### `frontend/src/components/FilterBar.jsx`
- **Props**: `filters` object, `onChange`
- **Fields**: status dropdown (all/open/pending/resolved/closed), priority dropdown (all/low/medium/high/urgent), assignee dropdown (admin/agent: list of org agents; customer: hidden)
- **Reset**: "Clear filters" button resets all to default
- **Layout**: horizontal row on desktop, stacked on mobile

### `frontend/src/components/SearchBar.jsx`
- **Props**: `value`, `onChange`, `onSearch`
- **Field**: text input with search icon
- **Debounce**: 300ms debounce before triggering search (or Enter key)
- **Clear**: X button to clear search

### `frontend/src/components/StatusBadge.jsx`
- **Props**: `status` string
- **Colors**: open=red, pending=amber, resolved=green, closed=gray
- **Shape**: rounded pill/badge

### `frontend/src/components/PriorityBadge.jsx`
- **Props**: `priority` string
- **Colors**: low=gray, medium=blue, high=amber, urgent=red
- **Shape**: rounded pill/badge

### `frontend/src/components/UserAvatar.jsx`
- **Props**: `name` string, `size` (sm/md/lg)
- **Display**: initials in a colored circle (hash name to color), or image if available
- **Tooltip**: full name on hover

### `frontend/src/components/LoadingSpinner.jsx`
- **Props**: `size` (sm/md/lg), `fullPage` (boolean)
- **Display**: centered spinning SVG, optional overlay for full page

### `frontend/src/components/EmptyState.jsx`
- **Props**: `title`, `message`, `icon` (optional), `action` (optional button)
- **Display**: centered icon + text + optional action button
- **Use**: no tickets found, no search results, no comments yet

### `frontend/src/components/ErrorBoundary.jsx`
- **Class component**: catches React errors
- **Display**: fallback UI with error message, "Reload" button
- **Logging**: `console.error` the error info

### `frontend/src/components/Modal.jsx`
- **Props**: `isOpen`, `onClose`, `title`, `children`, `footer` (optional)
- **Behavior**: overlay with backdrop blur, close on Escape or click outside
- **Accessibility**: focus trap, aria attributes

### `frontend/src/components/ConfirmDialog.jsx`
- **Props**: `isOpen`, `onConfirm`, `onCancel`, `title`, `message`, `confirmText`, `cancelText`, `danger` (boolean for red confirm button)
- **Use**: delete ticket, logout confirmation, etc.

---

## 6. Routing (Task F-006)

### `frontend/src/App.jsx`
```jsx
import { Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { DashboardPage } from './pages/DashboardPage';
import { TicketDetailPage } from './pages/TicketDetailPage';
import { NewTicketPage } from './pages/NewTicketPage';
import { EditTicketPage } from './pages/EditTicketPage';
import { AdminPage } from './pages/AdminPage';
import { AuthGuard } from './components/AuthGuard';

function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/" element={<AuthGuard><DashboardPage /></AuthGuard>} />
        <Route path="/dashboard" element={<AuthGuard><DashboardPage /></AuthGuard>} />
        <Route path="/tickets/new" element={<AuthGuard><NewTicketPage /></AuthGuard>} />
        <Route path="/tickets/:id" element={<AuthGuard><TicketDetailPage /></AuthGuard>} />
        <Route path="/tickets/:id/edit" element={<AuthGuard><EditTicketPage /></AuthGuard>} />
        <Route path="/admin" element={<AuthGuard><AdminPage /></AuthGuard>} />
      </Routes>
    </AuthProvider>
  );
}

export default App;
```

---

## 7. State Management (Task F-007)

### Global state (AuthContext)
- `user` object: id, name, email, role, organization_id
- `loading` boolean: initial auth check
- `login`, `register`, `logout` functions
- `isAdmin`, `isAgent`, `isCustomer` helpers

### Local/page state (useState + hooks)
- `useTickets`: tickets list, filters, pagination, loading, error
- `useTicket`: single ticket detail, loading, error
- `useComments`: comments list, loading, add function
- `useLocalStorage`: persistent UI preferences (e.g. sidebar collapsed)

### No Redux/Zustand needed: React Context + custom hooks sufficient for MVP scope.

---

## 8. Forms & Validation (Task F-008)

### Form patterns
- All forms use controlled components (`useState` for each field)
- Validation on submit + on blur for touched fields
- Error messages displayed below each field in red text
- Submit button disabled while `isSubmitting` or `!isValid`

### Ticket form validation rules
- `subject`: required, max 255 chars
- `description`: required, min 10 chars
- `priority`: required, one of `low/medium/high/urgent`
- `tags`: optional, comma-separated, max 10 tags, each max 50 chars
- `status` (edit only): optional, one of `open/pending/resolved/closed`

### Comment form validation
- `body`: required, min 1 char, max 5000 chars
- `is_internal`: optional boolean (admin/agent only)

### Auth form validation
- `email`: required, valid email format
- `password`: required, min 8 chars
- `password_confirmation`: required, must match password
- `organization_name`: required, max 255 (register only)
- `name`: required, max 255 (register only)

---

## 9. Error Handling & Loading States (Task F-009)

### API errors
- **401**: auto-logout via interceptor
- **403**: display "You don't have permission to do this." toast/alert
- **404**: display "Not found" page or message
- **422**: show field-level validation errors from API response
- **500**: display "Something went wrong. Please try again." with retry button
- **Network error**: display "Connection failed. Check your internet."

### Loading states
- **Page load**: full-page spinner overlay
- **List loading**: inline spinner or skeleton cards
- **Form submit**: button disabled + spinner, prevent double-submit
- **Lazy loading**: "Load more" button or infinite scroll with spinner

### Toast/notification system (simple, no library)
- `useToast` hook or `ToastContext` with `addToast(message, type)` function
- Types: `success`, `error`, `info`
- Auto-dismiss after 5 seconds
- Max 3 toasts stacked top-right

---

## 10. Responsive Design (Task F-010)

### Breakpoints (Tailwind defaults)
- `sm`: 640px — mobile
- `md`: 768px — tablet
- `lg`: 1024px — desktop
- `xl`: 1280px — wide desktop

### Layout rules
- **Sidebar**: hidden on mobile, toggle via hamburger menu
- **Ticket list**: 1 column mobile, 2 columns tablet, 3 columns desktop
- **Ticket detail**: stacked on mobile, side-by-side on desktop (info + comments)
- **Filter bar**: stacked on mobile, horizontal row on desktop
- **Font sizes**: responsive `text-sm` to `text-base` to `text-lg`
- **Padding**: `p-4` mobile, `p-6` tablet, `p-8` desktop

---

## 11. Accessibility (Task F-011)

- All form inputs have associated `<label>` elements
- Buttons have `aria-label` when icon-only
- Color is not the only indicator of status (icons + text + color)
- Keyboard navigation works for all interactive elements
- Focus states visible (ring outline)
- Modal traps focus and closes on Escape
- `ErrorBoundary` provides fallback for crashes
