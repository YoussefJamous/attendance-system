# Business Rules

## Authentication

- Users authenticate using email and password.
- Successful login creates a Laravel Sanctum API token.
- Logout revokes only the current access token.
- Registration, password reset, email verification, two-factor authentication, and social login are outside the current authentication milestone.

---

## Users

- HR creates employee accounts.
- Employees cannot register themselves.
- Every employee must have a user account.

---

## Employees

- Employee information is managed separately from authentication information.
- Every employee belongs to exactly one department.
- New employees receive a temporary password.
- Employee accounts are active by default.
- Employee records may be restored after deletion.

---

## Departments

- Departments organize employees within the organization.
- Department names must be unique.
- Department codes must be unique.
- Department codes are stored in uppercase.
- Departments will be used as the basis for employee number generation in a future version.

---

## Attendance

Attendance rules are documented separately in `attendance-design.md`.