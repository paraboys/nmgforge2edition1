# PulseDesk UI Flow

## 1. Application Structure and Navigation
The React frontend is structured around two main layouts:
- **Public Layout:** For authentication (Login/Register).
- **Authenticated Layout:** Includes a Sidebar navigation, Top Header (User Profile, Notifications), and Main Content area.

### Navigation (Sidebar)
- **Dashboard:** `/` (Overview metrics)
- **Tickets:** `/tickets` (List of all accessible tickets)
- **My Tickets:** `/tickets?assignee=me` (Quick filter)
- **Settings:** `/settings` (Admin only - manage tags, users)

## 2. Pages

### A. Authentication
- **Page:** `LoginPage` (`/login`)
- **Components:** `AuthForm`, `Input`, `Button`.
- **API Integration:** `POST /api/login`. Stores Sanctum token and User object in Context. Redirects to `/`.

### B. Dashboard
- **Page:** `DashboardPage` (`/`)
- **Components:** `MetricCard` (Open Tickets, Breached SLAs), `RecentActivityList`.
- **API Integration:** `GET /api/dashboard/metrics` (if available) or compute from `GET /api/tickets`.

### C. Ticket List
- **Page:** `TicketListPage` (`/tickets`)
- **Components:**
  - `TicketFilterBar` (Search input, Status dropdown, Priority dropdown)
  - `TicketTable` (Columns: ID, Subject, Requester, Assignee, Status, Priority, Created At)
  - `Pagination`
  - `CreateTicketModal` (Triggered via "New Ticket" button)
- **State Management:** Query params for filters. React Query `useQuery` for fetching.
- **API Integration:** `GET /api/tickets`

### D. Ticket Detail
- **Page:** `TicketDetailPage` (`/tickets/:id`)
- **Layout:**
  - **Main Column:** Ticket Header (Subject, Requester), `ConversationList`, `ReplyBox` (Rich text or plain text area, toggle for "Internal Note" / "Public Reply").
  - **Sidebar Column:** Ticket Meta (Status select, Priority select, Assignee select, Tags).
- **State Management:** React Query for ticket data and conversations. Mutations for updating ticket or posting reply.
- **API Integration:**
  - `GET /api/tickets/{id}`
  - `GET /api/tickets/{id}/conversations`
  - `POST /api/tickets/{id}/conversations`
  - `PUT /api/tickets/{id}` (When changing status/assignee from the sidebar)

## 3. Reusable Components
- `Button`: Standardized styled buttons (Primary, Secondary, Danger, Ghost).
- `Badge`: To show Status (e.g., Green for resolved, Red for open) and Priority.
- `Avatar`: Displays User Initials or Profile Picture.
- `Modal`: Accessible dialog for actions like Create Ticket.
- `Select` / `Input`: Form controls.
- `Spinner` / `Skeleton`: Loading states.

## 4. State Management Strategy
- **Global State (AuthContext):**
  - Stores the `user` object and authentication status (`isAuthenticated`).
  - Provides a `logout` function.
- **Server State (React Query / SWR):**
  - Used for all API data fetching (`useTickets`, `useTicket(id)`).
  - Handles caching, background refetching, and loading/error states.
  - Mutations for POST/PUT/DELETE requests with cache invalidation (e.g., refetch tickets after creating one).
- **Local State (useState/useReducer):**
  - Used for UI-only state (e.g., modal open/close, toggle internal note).

## 5. Styling
- **Tailwind CSS:** Utility-first styling.
- Design tokens defined in `tailwind.config.js` to match the "Dynamic Design" and "Rich Aesthetics" guidelines (dark mode by default, glassmorphism on modals, smooth gradients).
