# React Component Tree

```text
App
├── BrowserRouter
│   └── AuthProvider
│       ├── Routes
│       │   ├── Route (Public: /login)
│       │   │   └── LoginPage
│       │   │       └── AuthForm
│       │   │           ├── Input (Email)
│       │   │           ├── Input (Password)
│       │   │           └── Button (Submit)
│       │   │
│       │   └── Route (Protected: /)
│       │       └── ProtectedLayout
│       │           ├── Sidebar
│       │           │   └── NavLinks (Dashboard, Tickets, Settings)
│       │           ├── Header
│       │           │   └── UserAvatar / LogoutButton
│       │           └── MainContent
│       │               ├── Route (/)
│       │               │   └── DashboardPage
│       │               │       ├── MetricCard (Open Tickets)
│       │               │       ├── MetricCard (SLA Breaches)
│       │               │       └── RecentActivityList
│       │               │
│       │               ├── Route (/tickets)
│       │               │   └── TicketListPage
│       │               │       ├── TicketFilterBar
│       │               │       │   ├── Input (Search)
│       │               │       │   ├── Select (Status)
│       │               │       │   └── Select (Priority)
│       │               │       ├── Button (New Ticket) -> toggles Modal
│       │               │       ├── CreateTicketModal
│       │               │       ├── TicketTable
│       │               │       │   └── Badge (Status/Priority)
│       │               │       └── Pagination
│       │               │
│       │               ├── Route (/tickets/:id)
│       │               │   └── TicketDetailPage
│       │               │       ├── MainColumn
│       │               │       │   ├── TicketHeader
│       │               │       │   ├── ConversationList
│       │               │       │   │   └── ConversationItem
│       │               │       │   └── ReplyBox
│       │               │       │       ├── Textarea
│       │               │       │       ├── Toggle (Internal Note)
│       │               │       │       └── Button (Send)
│       │               │       └── SidebarColumn
│       │               │           ├── Select (Update Status)
│       │               │           ├── Select (Update Priority)
│       │               │           ├── Select (Assignee)
│       │               │           └── TagList
│       │               │
│       │               └── Route (/settings) (Admin Only)
│       │                   └── SettingsPage
│       │                       ├── Tabs
│       │                       │   ├── UserTab (UserTable)
│       │                       │   ├── TagTab (TagManager)
│       │                       │   └── SLATab (SlaPolicyManager)
```

## Component Dependencies & Rules
- `AuthContext` wraps the entire app to provide user state globally.
- `ProtectedLayout` handles redirection if the user is not authenticated.
- Forms should use standard HTML form submission patterns via `onSubmit` calling `e.preventDefault()`.
- Modals should use a portal or absolute positioning with an overlay.
