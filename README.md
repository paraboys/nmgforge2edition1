# PulseDesk -- Forge 2 / Edition 1 (forge2-parassingh)

A multi-tenant support-desk SaaS, BUILT BY ORCHESTRATING Hermes + OpenClaw over Slack.
This project implements the Forge 2 requirements fully.

## Stack (required)
Laravel 11 . PHP 8.2 . MySQL 8 / SQLite . Laravel Sanctum . React 19 . Vite . Tailwind

## EastRouter models I used
- Hermes (planning / product owner): Google Gemini Pro
- OpenClaw (coding): Google Gemini Pro

## How to run (EXACT -- a judge will run these from a fresh clone)
### Backend (Laravel)
    cd backend
    cp .env.example .env
    # The default is set to sqlite for immediate running. For MySQL, set DB_CONNECTION=mysql in .env
    composer install
    php artisan key:generate
    touch database/database.sqlite
    php artisan migrate --seed
    php artisan serve             # http://127.0.0.1:8000
### Frontend (React + Vite)
    cd frontend
    npm install
    npm run dev                   # http://127.0.0.1:5173

## Demo logins (from the seeder)
- admin@demo.com / password (Admin User)
- agent@demo.com / password (Agent User)
- customer@demo.com / password (Customer User)

## Live URL
runs locally per the steps above

## Where my evidence lives
- agents/        -- real Hermes + OpenClaw configs (secrets redacted)
- agent-log.md   -- the human->Hermes->OpenClaw loop
- sprints/       -- one doc per sprint
- slack-export/  -- Slack export screenshots
- evidence/screenshots/ -- app, agents-running, CI screenshots
