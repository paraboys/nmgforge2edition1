# PulseDesk — Testing Plan

> Stack: Pest / PHPUnit (backend) · Build smoke (frontend)

---

## Backend Testing Strategy

### Test Organization
```
tests/
├── Feature/
│   ├── AuthTest.php
│   ├── TenantIsolationTest.php
│   ├── TicketTest.php
│   ├── CommentTest.php
│   ├── UserTest.php
│   └── RoleBasedAccessTest.php
└── Unit/
    ├── TenantScopeTest.php
    └── PolicyTest.php
```

### AuthTest.php (10 tests minimum)

| # | Test name | What it validates |
|---|---|---|
| 1 | `test_user_can_register_with_new_organization` | POST /register creates org + admin user, returns token |
| 2 | `test_register_requires_valid_email` | 422 on invalid email |
| 3 | `test_register_requires_password_confirmation` | 422 when passwords don't match |
| 4 | `test_user_can_login_with_valid_credentials` | POST /login returns token + user |
| 5 | `test_login_fails_with_invalid_credentials` | 401 on wrong password |
| 6 | `test_me_returns_authenticated_user` | GET /me returns user + org |
| 7 | `test_me_fails_without_token` | 401 without auth header |
| 8 | `test_logout_revokes_token` | POST /logout deletes token, subsequent requests 401 |
| 9 | `test_email_must_be_unique` | 422 when registering duplicate email |
| 10 | `test_password_must_be_min_8_chars` | 422 when password < 8 chars |

### TenantIsolationTest.php (6 tests minimum)

| # | Test name | What it validates |
|---|---|---|
| 1 | `test_cross_org_user_cannot_read_other_org_tickets` | User in Org A gets 404 on Org B ticket (scope hides it) |
| 2 | `test_cross_org_user_cannot_list_other_org_tickets` | Org A user sees 0 tickets from Org B in list |
| 3 | `test_cross_org_user_cannot_update_other_org_tickets` | 403 or 404 on PUT to Org B ticket |
| 4 | `test_cross_org_user_cannot_delete_other_org_tickets` | 403 or 404 on DELETE to Org B ticket |
| 5 | `test_cross_org_user_cannot_see_other_org_comments` | Comments endpoint returns empty for other org |
| 6 | `test_cross_org_user_cannot_see_other_org_users` | User list only shows same org users |

### TicketTest.php (12 tests minimum)

| # | Test name | What it validates |
|---|---|---|
| 1 | `test_admin_can_list_all_org_tickets` | GET /tickets returns all tickets in admin's org |
| 2 | `test_agent_can_list_all_org_tickets` | GET /tickets returns all tickets in agent's org |
| 3 | `test_customer_can_list_only_own_tickets` | GET /tickets returns only requester_id = self |
| 4 | `test_admin_can_create_ticket` | POST /tickets creates ticket, sets requester |
| 5 | `test_customer_can_create_ticket` | POST /tickets creates ticket, sets requester to self |
| 6 | `test_admin_can_update_any_ticket_in_org` | PUT /tickets/{id} updates any ticket in org |
| 7 | `test_customer_can_update_own_ticket_subject_description` | PUT updates subject + description only |
| 8 | `test_customer_cannot_update_ticket_status` | 403 when customer tries status change |
| 9 | `test_customer_cannot_update_ticket_priority` | 403 when customer tries priority change |
| 10 | `test_customer_cannot_update_ticket_assignee` | 403 when customer tries assignee change |
| 11 | `test_admin_can_delete_ticket` | DELETE returns 204 |
| 12 | `test_agent_cannot_delete_ticket` | 403 when agent tries delete |
| 13 | `test_list_supports_status_filter` | ?status=open returns only open tickets |
| 14 | `test_list_supports_priority_filter` | ?priority=high returns only high priority |
| 15 | `test_list_supports_pagination` | meta contains pagination info |
| 16 | `test_search_filters_by_subject` | ?q=keyword returns matching tickets |

### CommentTest.php (6 tests minimum)

| # | Test name | What it validates |
|---|---|---|
| 1 | `test_admin_can_add_public_comment` | POST comment, is_internal=false |
| 2 | `test_admin_can_add_internal_note` | POST comment, is_internal=true |
| 3 | `test_agent_can_add_internal_note` | POST comment, is_internal=true |
| 4 | `test_customer_cannot_add_internal_note` | is_internal forced to false for customer |
| 5 | `test_customer_cannot_see_internal_notes` | GET /comments returns only public for customer |
| 6 | `test_admin_can_see_all_comments` | GET /comments returns both public and internal |
| 7 | `test_customer_can_comment_on_own_ticket` | POST succeeds on own ticket |
| 8 | `test_customer_cannot_comment_on_other_ticket` | 403 on other customer's ticket |

### UserTest.php (5 tests minimum)

| # | Test name | What it validates |
|---|---|---|
| 1 | `test_admin_can_list_org_users` | GET /users returns all users in org |
| 2 | `test_agent_can_list_org_users` | GET /users returns all users in org |
| 3 | `test_customer_cannot_list_users` | 403 for customer |
| 4 | `test_admin_can_create_agent` | POST /users with role=agent creates agent |
| 5 | `test_admin_can_create_customer` | POST /users with role=customer creates customer |
| 6 | `test_non_admin_cannot_create_user` | 403 for agent and customer |
| 7 | `test_user_list_is_tenant_scoped` | only same-org users visible |

### RoleBasedAccessTest.php (4 tests minimum)

| # | Test name | What it validates |
|---|---|---|
| 1 | `test_admin_has_full_ticket_access` | can create, read, update, delete any org ticket |
| 2 | `test_agent_has_limited_ticket_access` | can create, read, update; cannot delete |
| 3 | `test_customer_has_restricted_ticket_access` | can create, read own; cannot update status/priority |
| 4 | `test_unauthenticated_requests_are_blocked` | 401 on all protected endpoints |

### Unit Tests

#### TenantScopeTest.php
- `test_scope_applies_when_authenticated` — query includes organization_id filter
- `test_scope_does_not_apply_when_guest` — no filter added for unauthenticated requests
- `test_scope_prevents_cross_org_data_leak` — SQL assertion that WHERE clause exists

#### PolicyTest.php
- `test_ticket_policy_view_any_returns_true` — scope handles filtering
- `test_ticket_policy_view_denies_cross_org` — false when org mismatch
- `test_ticket_policy_view_allows_customer_own` — true for own ticket
- `test_ticket_policy_delete_allows_admin_only` — true for admin, false for agent/customer

---

## Frontend Testing Strategy

### Build Test
- `npm run build` completes without errors
- No TypeScript errors (if using TS, otherwise skip)
- No ESLint errors (if configured)

### Manual Test Checklist (for judges/QA)

| # | Test | Steps | Expected |
|---|---|---|---|
| 1 | Register new org | Fill register form, submit | Redirect to dashboard, token stored |
| 2 | Login existing user | Fill login form, submit | Redirect to dashboard, user loaded |
| 3 | Logout | Click logout button | Token removed, redirect to login |
| 4 | Create ticket | Fill new ticket form, submit | Ticket appears in list, redirect to detail |
| 5 | View ticket list | Open dashboard | Tickets load, cards display correctly |
| 6 | Filter by status | Select status filter | List updates to show only matching tickets |
| 7 | Filter by priority | Select priority filter | List updates to show only matching tickets |
| 8 | Search tickets | Type in search box | List updates with search results |
| 9 | View ticket detail | Click ticket card | Detail page loads with comments |
| 10 | Add public comment | Fill comment form, submit | Comment appears in thread |
| 11 | Admin adds internal note | Check "Internal" checkbox, submit | Note appears with internal styling |
| 12 | Customer cannot see internal | Login as customer, view same ticket | Internal note is hidden |
| 13 | Admin changes status | Select new status from dropdown | Status updates, badge changes color |
| 14 | Admin assigns ticket | Select agent from assignee dropdown | Assignee updates |
| 15 | Customer cannot change status | Login as customer, open edit | Status dropdown is hidden or disabled |
| 16 | Delete ticket | Admin clicks delete, confirms | Ticket removed, redirect to dashboard |
| 17 | Responsive mobile | Resize to mobile width | Sidebar collapses, layout stacks |
| 18 | 404 page | Visit non-existent route | Not found page displays |
| 19 | Direct link to ticket | Open `/tickets/1` directly | Ticket loads if authorized |
| 20 | Unauthorized access | Customer tries `/admin` | Redirect to dashboard |

---

## CI Testing (GitHub Actions)

### Workflow (`ci.yml`)
1. **Checkout** code
2. **Setup PHP 8.2** + MySQL 8 service
3. **Backend install**: `composer install`
4. **Backend configure**: copy `.env.example`, generate key, set DB credentials
5. **Backend migrate**: `php artisan migrate --force`
6. **Backend test**: `php artisan test` (runs Pest/PHPUnit)
7. **Frontend install**: `npm install` in `frontend/` directory
8. **Frontend build**: `npm run build` — must exit 0

### Test criteria for CI green
- All backend tests pass (exit code 0)
- Frontend build succeeds (exit code 0)
- No PHP syntax errors
- No uncommitted migrations (check with `php artisan migrate:status`)

---

## Test Data Factories

### OrganizationFactory
```php
public function definition(): array
{
    return [
        'name' => fake()->company(),
        'slug' => fake()->unique()->slug(),
    ];
}
```

### UserFactory
```php
public function definition(): array
{
    return [
        'organization_id' => Organization::factory(),
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'role' => fake()->randomElement(['admin', 'agent', 'customer']),
    ];
}
```

### TicketFactory
```php
public function definition(): array
{
    return [
        'organization_id' => Organization::factory(),
        'requester_id' => User::factory(),
        'assignee_id' => null,
        'subject' => fake()->sentence(4),
        'description' => fake()->paragraph(3),
        'status' => fake()->randomElement(['open', 'pending', 'resolved', 'closed']),
        'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
        'tags' => fake()->words(2),
    ];
}
```

### CommentFactory
```php
public function definition(): array
{
    return [
        'ticket_id' => Ticket::factory(),
        'author_id' => User::factory(),
        'body' => fake()->paragraph(2),
        'is_internal' => false,
    ];
}
```

---

## Coverage Goals

| Layer | Minimum Coverage | Priority |
|---|---|---|
| Auth endpoints | 100% | Must-have |
| Tenant isolation | 100% | Must-have (security-gated) |
| Ticket CRUD | 90% | Must-have |
| Comment CRUD | 80% | Must-have |
| User management | 70% | Should-have |
| Role-based access | 100% | Must-have |
| Frontend build | Passes | Must-have |
