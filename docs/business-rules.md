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

- HR must create the global attendance configuration before employees can clock in or out.
- Employees require an assigned shift before they can clock in or out.
- Clock-in and clock-out actions use their server-managed `created_at` timestamp.
- Direct clock-in and clock-out actions require an image; correction timelines do not.
- Attendance actions must alternate between clock-in and clock-out.
- Employees record direct actions through one endpoint, and the system derives the next action from the latest log.
- Employees require the `attendance.record` permission to record an action.
- Consecutive attendance actions must respect the configured minimum interval.
- The configured attendance method is not validated in Version 1.
- Employees submit one complete proposed timeline and a note for each correction request.
- A pending correction does not alter attendance logs; only HR approval replaces that day's logs.
- An employee may have only one pending correction for the same attendance date.

---

## Shifts

- Employees may be created without a shift assignment.
- Employees require a shift assignment before they can use attendance endpoints.
- A shift contains one or more shift days.
- A shift can have only one schedule for each weekday.
- Each shift day defines its working hours and total break duration.
- Version 1 does not apply special validation or calculation rules to overnight schedules.
- A shift assigned to employees cannot be deleted.

---

## Holidays

- HR manages holidays.
- A holiday is defined by a name, optional description, and date range.
- When no end date is provided, the holiday ends on its start date.
- Holiday date ranges may overlap.
- Holiday attendance does not automatically create approved overtime.
