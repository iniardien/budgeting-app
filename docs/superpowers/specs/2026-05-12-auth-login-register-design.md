# Auth Login and Register Refresh Design

## Objective

Refresh the current authentication entry point so the budgeting app feels more polished and complete for a college project, while keeping implementation simple and Laravel-native.

This phase covers:

- a more professional login presentation
- a registration flow for demo and coursework use
- a single authentication page with login and register modes
- backend wiring for login, register, logout, and post-register auto-login

## Scope

The application will expose one guest-facing authentication page that supports both:

- user login
- user registration

Registration is intentionally lightweight:

- open to guests
- no email verification
- no password reset flow
- no admin approval

After a successful registration, the user is automatically authenticated and redirected to the dashboard.

## Goals

- Make the auth page look cleaner, more modern, and more intentional
- Keep the UI consistent with the current budgeting dashboard styling
- Reduce friction by using one page for both login and registration
- Preserve clear Laravel validation and form handling
- Add automated tests for the new auth behavior

## Non-Goals

- Email verification
- Forgot password or reset password
- Social login
- Role management
- Profile completion flow
- Account settings beyond basic name and email captured at registration

## Recommended Approach

Use a single Blade view with two separate forms and a mode switch.

This approach fits the current Laravel structure best because:

- login and register can keep separate POST endpoints
- validation rules remain straightforward
- old input and error rendering can target the correct form
- feature tests stay simple and explicit

## User Experience

### Entry Page

Guests visit `/login` and see one authentication card containing:

- a page heading and supporting copy
- a segmented switch for `Masuk` and `Daftar`
- the active form for the chosen mode
- contextual helper text
- validation errors rendered near the relevant fields

The existing left-side branding panel remains, but the copy should better support both login and registration.

### Login Mode

Login mode contains:

- email
- password
- remember me
- primary submit button
- a secondary inline prompt to switch to registration

### Register Mode

Register mode contains:

- full name
- email
- password
- password confirmation
- primary submit button
- a secondary inline prompt to switch back to login

### Mode Persistence

If login validation fails, the page returns with login mode active.

If registration validation fails, the page returns with register mode active and preserves non-password fields using normal Laravel old input behavior.

### Success Behavior

- successful login redirects to the intended page or dashboard
- successful registration logs the user in immediately and redirects to the dashboard
- logout keeps the current behavior and redirects back to login

## Backend Design

### Routing

Guest routes:

- `GET /login` renders the combined auth page
- `POST /login` handles session login
- `POST /register` handles account creation

Authenticated route:

- `POST /logout` destroys the session

### Controllers

Keep session logic in `AuthenticatedSessionController`.

Add a dedicated registration controller responsible for:

- validating registration input
- creating the user
- logging the user in
- redirecting to the dashboard

### Validation Rules

Registration should validate:

- `name`: required, string, reasonable max length
- `email`: required, email, unique in users table
- `password`: required, confirmed, minimum sensible length

Login should keep the current required email and password checks.

### Active Mode Handling

The guest auth page should receive enough state to determine which tab is active.

Recommended mechanism:

- default to login mode on first load
- force register mode when redirected back from failed registration using flashed input such as `auth_mode=register`
- force login mode when redirected back from failed login using flashed input such as `auth_mode=login`

This avoids brittle client-only state and works naturally with full-page Laravel responses.

## View Design

Use the existing `resources/views/auth/login.blade.php` as the combined auth entry point instead of creating a second page.

Planned visual refinements:

- stronger typography hierarchy
- clearer spacing between title, helper text, and form areas
- a more explicit segmented control for mode switching
- improved field descriptions and error styling
- consistent button sizing and emphasis
- slightly richer branding copy on the left panel without making the page noisy

The page should remain mobile-friendly and keep the current two-column desktop layout.

## Data Model Impact

No schema changes are required because the default users table already supports:

- `name`
- `email`
- `password`

## Testing Strategy

Add or update feature tests to cover:

- guest can view the auth page
- user can log in with valid credentials
- user can log out
- guest can register with valid data
- registration failure returns validation errors
- registration auto-authenticates the new user

Tests should disable Vite where needed, following the current auth test pattern.

## Risks and Mitigations

### Risk: both forms share one page and errors appear under the wrong mode

Mitigation: flash explicit `auth_mode` input and use it to control which form is visible after redirects.

### Risk: combined page becomes visually crowded on mobile

Mitigation: keep only one form visible at a time and maintain generous spacing with concise helper text.

### Risk: registration logic drifts from Laravel conventions

Mitigation: keep server-side validation, password hashing, and authentication flow close to standard Laravel patterns.

## Success Criteria

This work is complete when:

- guests can open one polished auth page
- users can switch between login and registration on that page
- registration creates an account and signs the user in automatically
- failed submissions return to the correct auth mode with clear errors
- auth feature tests cover the new registration flow and still pass
