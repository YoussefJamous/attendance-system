# Business Rules

## Authentication

- Users authenticate using email and password.
- Successful login creates a Laravel Sanctum API token.
- Logout revokes only the current access token.
- Authenticated user responses expose only safe profile fields required by the API.
- Registration, password reset, email verification, two-factor authentication, and social login are outside the current authentication milestone.
