# Complete File Structure

## Implementation Order & Dependencies
This document serves as the global blueprint. It must be read first before implementing any specific module.

## Root Directory (`forge2-<yourname>`)

```text
/
├── README.md                           # Project overview, setup instructions
├── SUBMISSION.md                       # Hackathon submission checklist
├── .env.example                        # Global environment variables
├── .github/
│   └── workflows/
│       └── ci.yml                      # CI/CD pipeline
├── backend/                            # Laravel Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/                # REST Controllers (Auth, Ticket, Dashboard)
│   │   │   ├── Requests/               # FormRequests for validation
│   │   │   ├── Resources/              # API Resources for JSON formatting
│   │   │   └── Middleware/             # Custom middleware (if any)
│   │   ├── Models/                     # Eloquent Models (User, Ticket, etc.)
│   │   ├── Policies/                   # Authorization policies
│   │   └── Scopes/                     # Global Scopes (TenantScope)
│   ├── database/
│   │   ├── migrations/                 # Ordered migration files
│   │   └── seeders/                    # DatabaseSeeders
│   ├── routes/
│   │   └── api.php                     # API routes
│   └── tests/
│       └── Feature/                    # Feature tests
└── frontend/                           # React Frontend
    ├── public/
    ├── src/
    │   ├── assets/                     # Images, icons
    │   ├── components/                 # Reusable UI components
    │   │   ├── common/                 # Buttons, inputs, modals
    │   │   └── layout/                 # Sidebar, Header
    │   ├── context/                    # React Contexts (AuthContext)
    │   ├── hooks/                      # Custom hooks
    │   ├── pages/                      # Page-level components
    │   ├── services/                   # Axios API clients
    │   ├── utils/                      # Helper functions
    │   ├── App.jsx                     # Root application component
    │   ├── main.jsx                    # Entry point
    │   └── index.css                   # Tailwind styles
    ├── package.json
    ├── tailwind.config.js
    └── vite.config.js
```
