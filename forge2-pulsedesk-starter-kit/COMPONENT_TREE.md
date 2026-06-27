# PulseDesk — Component Tree

> Visual reference of all React components, their props, children, and data flow.

---

## Top-Level Hierarchy

```
App.jsx
└── AuthProvider
    └── BrowserRouter
        └── Routes
            ├── LoginPage (public)
            ├── RegisterPage (public)
            └── AuthGuard
                └── Layout
                    ├── Navbar
                    ├── Sidebar
                    └── <page content>
                        ├── DashboardPage
                        │   ├── FilterBar
                        │   ├── SearchBar
                        │   ├── TicketList
                        │   │   └── TicketCard (xN)
                        │   └── Pagination (inline in TicketList)
                        ├── TicketDetailPage
                        │   ├── TicketHeader
                        │   ├── StatusBadge
                        │   ├── PriorityBadge
                        │   ├── TicketMeta (requester, assignee, dates)
                        │   ├── TicketActions (admin/agent: edit, assign, status change)
                        │   ├── CommentThread
                        │   │   └── CommentItem (xN)
                        │   │       ├── UserAvatar
                        │   │       ├── AuthorMeta
                        │   │       └── CommentBody
                        │   └── CommentForm
                        ├── NewTicketPage
                        │   └── TicketForm
                        ├── EditTicketPage
                        │   └── TicketForm (isEdit=true)
                        └── AdminPage
                            ├── UserTable
                            └── UserForm
```

---

## Component Reference

### AuthGuard
- **Type**: wrapper / route guard
- **Props**: `children`
- **Logic**: if `!user` -> redirect `/login`; if `loading` -> `<LoadingSpinner fullPage/>`
- **Used in**: every protected route

### Layout
- **Type**: page shell
- **Props**: `children`
- **Children**: `Navbar`, `Sidebar`, `<main>`
- **State**: `sidebarOpen` (mobile toggle)
- **Used in**: all authenticated pages

### Navbar
- **Type**: top bar
- **Props**: none (uses `useAuth`)
- **Displays**: logo, user name + role badge, logout button
- **Actions**: logout -> clear state -> redirect `/login`

### Sidebar
- **Type**: navigation
- **Props**: `isOpen` (mobile), `onClose`
- **Links**: Dashboard, New Ticket, Admin (admin only)
- **Active state**: `useLocation` for route matching
- **Responsive**: collapsible drawer on mobile, fixed sidebar on desktop

### LoginPage
- **Type**: full page
- **State**: `formData` {email, password}, `errors`, `isSubmitting`
- **Actions**: `auth.login()` -> redirect `/dashboard`
- **Effects**: if logged in, auto-redirect to dashboard

### RegisterPage
- **Type**: full page
- **State**: `formData` {organization_name, name, email, password, password_confirmation}, `errors`, `isSubmitting`
- **Actions**: `auth.register()` -> redirect `/dashboard`

### DashboardPage
- **Type**: full page
- **State**: `filters` {status, priority, assignee, q}, `page`
- **Data**: `useTickets(filters)` hook
- **Components**: `FilterBar`, `SearchBar`, `TicketList`
- **Actions**: navigate to `/tickets/:id` on card click

### FilterBar
- **Type**: form / controls
- **Props**: `filters` object, `onChange(filters)`
- **Fields**: status select, priority select, assignee select (admin/agent only), "Clear" button
- **Logic**: each change calls `onChange` with updated filters

### SearchBar
- **Type**: input
- **Props**: `value`, `onChange`, `onSearch`
- **Logic**: debounce 300ms, Enter triggers immediate search, X button clears

### TicketList
- **Type**: list container
- **Props**: `tickets` array, `meta` object, `loading` boolean, `onPageChange(page)`
- **Children**: `TicketCard` * N, `LoadingSpinner`, `EmptyState`
- **Layout**: responsive grid (1/2/3 columns)
- **Pagination**: prev/next buttons using `meta.current_page`, `meta.last_page`

### TicketCard
- **Type**: card item
- **Props**: `ticket` object
- **Displays**: subject (truncated), `StatusBadge`, `PriorityBadge`, requester avatar + name, assignee or "Unassigned", relative created date
- **Action**: onClick -> navigate to `/tickets/:id`
- **Hover**: shadow, cursor pointer

### TicketDetailPage
- **Type**: full page
- **State**: `ticket` from `useTicket(id)`, `comments` from `useComments(id)`, `activeTab` (details/comments)
- **Components**: `TicketHeader`, `StatusBadge`, `PriorityBadge`, `TicketMeta`, `TicketActions`, `CommentThread`, `CommentForm`
- **Actions**: admin/agent can change status, priority, assignee; all roles can add comments (internal gated)
- **Loading**: `LoadingSpinner` while fetching

### TicketHeader
- **Type**: section header
- **Props**: `subject`, `ticketId`
- **Displays**: subject as H1, ticket ID as subtitle

### TicketMeta
- **Type**: info grid
- **Props**: `ticket` object
- **Displays**: requester (avatar + name), assignee (avatar + name or "Unassigned"), created date, updated date
- **Admin/agent**: can click assignee to open assignment dropdown

### TicketActions
- **Type**: action bar
- **Props**: `ticket`, `onStatusChange`, `onPriorityChange`, `onAssign`, `onDelete`
- **Role-gated**: status/priority/assign visible to admin/agent; delete visible to admin only
- **Components**: dropdowns for status/priority/assignee, delete button with `ConfirmDialog`

### CommentThread
- **Type**: list container
- **Props**: `comments` array, `currentUser`
- **Children**: `CommentItem` * N
- **Logic**: filters out `is_internal` for customer role (redundant with backend, but safe)
- **Empty state**: `EmptyState` with "No comments yet" message

### CommentItem
- **Type**: list item
- **Props**: `comment` object, `isInternal` (computed from `comment.is_internal`)
- **Displays**: `UserAvatar`, author name + role badge, relative timestamp, body text
- **Styling**: internal notes have amber/yellow left border + "Internal" badge; public comments have blue/gray left border

### CommentForm
- **Type**: form
- **Props**: `onSubmit(data)`, `isInternalAllowed` (boolean from role)
- **State**: `body` string, `isInternal` boolean, `isSubmitting`
- **Validation**: body required, non-empty
- **Submit**: Ctrl+Enter or button click
- **Loading**: button disabled + spinner during submit

### NewTicketPage
- **Type**: full page
- **Components**: `TicketForm`
- **Actions**: onSubmit -> `ticketService.create()` -> redirect `/tickets/:id`

### EditTicketPage
- **Type**: full page
- **State**: `ticket` loaded via `useTicket(id)`
- **Components**: `TicketForm` (isEdit=true, initialData=ticket)
- **Actions**: onSubmit -> `ticketService.update()` -> redirect `/tickets/:id`
- **Admin only**: delete button with `ConfirmDialog` -> `ticketService.delete()` -> redirect `/dashboard`

### TicketForm
- **Type**: reusable form
- **Props**: `initialData` (optional), `onSubmit`, `onCancel`, `isEdit` (boolean), `isAdmin` (boolean), `isAgent` (boolean)
- **Fields**: subject (input), description (textarea), priority (select), tags (input), status (select, admin/agent only if edit)
- **State**: controlled form with `useState`, `touched` fields, `errors` object, `isSubmitting`
- **Validation**: real-time on blur for touched fields, full validation on submit
- **Effects**: if `isEdit` and `initialData`, populate fields on mount

### AdminPage
- **Type**: full page (admin-only)
- **State**: `users` from `useUsers()`, `showCreateForm` boolean
- **Components**: `UserTable`, `UserForm`
- **Actions**: create agent/customer, view org users
- **Redirect**: non-admin -> `/dashboard`

### UserTable
- **Type**: table
- **Props**: `users` array
- **Columns**: name, email, role, created date
- **Actions**: none for MVP (admin can view only; edit/delete deferred)

### UserForm
- **Type**: form
- **Props**: `onSubmit`, `onCancel`
- **Fields**: name, email, password, role (select: agent/customer)
- **Validation**: same as register but no organization fields
- **Submit**: `userService.create()` -> refresh list -> close form

### LoadingSpinner
- **Type**: presentational
- **Props**: `size` (sm/md/lg), `fullPage` (boolean)
- **Displays**: SVG spinner, centered

### EmptyState
- **Type**: presentational
- **Props**: `title`, `message`, `icon` (optional React node), `action` (optional {label, onClick})
- **Displays**: centered icon + text + optional button
- **Use**: no tickets, no comments, no search results

### ErrorBoundary
- **Type**: class component
- **State**: `hasError`, `error`
- **Displays**: fallback UI with error message, "Reload" button
- **Logging**: `console.error` error info

### Modal
- **Type**: overlay
- **Props**: `isOpen`, `onClose`, `title`, `children`, `footer` (optional)
- **Behavior**: backdrop blur, close on Escape or click outside, focus trap
- **Accessibility**: `aria-modal="true"`, focus trap

### ConfirmDialog
- **Type**: modal wrapper
- **Props**: `isOpen`, `onConfirm`, `onCancel`, `title`, `message`, `confirmText`, `cancelText`, `danger` (boolean)
- **Displays**: `Modal` with centered text + two buttons (cancel default, confirm primary or danger red)
- **Use**: delete ticket, logout, status change confirmation

### StatusBadge
- **Type**: presentational
- **Props**: `status` string
- **Colors**: open=red-500, pending=amber-500, resolved=green-500, closed=gray-500
- **Shape**: inline-flex, rounded-full, px-2 py-1, text-xs, uppercase, font-semibold

### PriorityBadge
- **Type**: presentational
- **Props**: `priority` string
- **Colors**: low=gray-500, medium=blue-500, high=amber-500, urgent=red-500
- **Shape**: same as StatusBadge, optionally with dot indicator

### UserAvatar
- **Type**: presentational
- **Props**: `name` string, `size` (sm/md/lg), `src` (optional image URL)
- **Display**: if `src` -> `<img>`; else -> colored circle with initials (2 chars max)
- **Color**: hash `name` to a Tailwind color palette (e.g., `bg-blue-500`, `text-white`)
- **Sizes**: sm=24px, md=32px, lg=40px

---

## Data Flow Summary

```
API (Laravel) 
  ← axios interceptors (api.js)
    ← service layer (authService, ticketService, commentService, userService)
      ← custom hooks (useTickets, useTicket, useComments)
        ← pages (DashboardPage, TicketDetailPage, ...)
          ← components (TicketList, TicketCard, CommentThread, ...)
            ← presentational (StatusBadge, UserAvatar, LoadingSpinner)

Auth state (AuthContext)
  ← hooks (useAuth)
    ← guards (AuthGuard)
      ← pages + components (Navbar, Sidebar, conditional UI)
```

---

## Reusability Matrix

| Component | Used By | Reusable |
|---|---|---|
| AuthGuard | all protected routes | yes |
| Layout | all protected pages | yes |
| Navbar | Layout | yes |
| Sidebar | Layout | yes |
| LoadingSpinner | everywhere | yes |
| EmptyState | TicketList, CommentThread, Dashboard | yes |
| ErrorBoundary | App root | yes (once) |
| Modal | ConfirmDialog | yes |
| ConfirmDialog | TicketActions, EditTicketPage, Navbar | yes |
| StatusBadge | TicketCard, TicketDetailPage | yes |
| PriorityBadge | TicketCard, TicketDetailPage | yes |
| UserAvatar | TicketMeta, CommentItem, Navbar | yes |
| TicketCard | TicketList | yes |
| TicketList | DashboardPage | yes |
| FilterBar | DashboardPage | yes |
| SearchBar | DashboardPage | yes |
| TicketForm | NewTicketPage, EditTicketPage | yes |
| CommentThread | TicketDetailPage | yes |
| CommentItem | CommentThread | yes |
| CommentForm | TicketDetailPage | yes |
| TicketActions | TicketDetailPage | yes |
| UserTable | AdminPage | yes |
| UserForm | AdminPage | yes |
