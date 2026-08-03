# Delivery Roadmap

This document tracks delivered and planned capabilities by domain. It distinguishes HR administration from employee self-service so that a model or permission is not mistaken for a completed workflow.

## Status Key

- Complete: implemented, tested, and available through the API.
- Partial: the foundation exists, but one or more user workflows remain.
- Planned: no implementation exists yet.

## Current Delivery Summary

| Domain | Status | HR | Employee |
| --- | --- | --- | --- |
| Infrastructure | Complete | N/A | N/A |
| Authentication | Partial | Can use login, logout, and me | Can use login, logout, and me |
| Authorization | Partial | HR role has management permissions | Employee role has limited permissions |
| Departments | Complete | Full management | Can view departments |
| Employees | Partial | Full employee lifecycle management | No profile endpoint yet |
| Shifts | Complete | Full shift and Shift Day management | Receives an optional shift assignment |
| Holidays | Complete | Full holiday management and Excel import | No holiday endpoint required yet |
| Attendance Tracking | Partial | Seeded system configuration and filtered attendance review are available | Can clock in, clock out, and view own attendance |
| Attendance Corrections | Complete | Can list, filter, approve, and reject correction requests | Can submit and view filtered own correction requests |
| Overtime | Planned | Will approve or reject overtime | Will submit overtime requests |
| Leave Management | Planned | Will manage leave policy and approvals | Will submit leave requests |
| Reports | Planned | Will access operational reports | Personal reports are not defined yet |
| Notifications | Planned | Will receive administrative notifications | Will receive account and workflow notifications |
| Manager Module | Planned | Will configure manager access | Manager workflows are not defined yet |

## Delivered Domains

### Infrastructure

Status: Complete

Delivered:

- Versioned API routes under `/api/v1`.
- Standard API success and error response formats.
- API exception rendering for validation, authentication, authorization, missing-model, and HTTP errors.
- UUID primary-key convention and Sanctum personal access tokens.

Remaining:

- No infrastructure work is currently required for Version 1.

### Authentication and User

Status: Partial

Models: `User`, Sanctum personal access tokens, roles, and permissions.

Delivered for HR and employees:

- Login with email and password.
- Logout that revokes only the current access token.
- Authenticated-user endpoint.
- Seeded HR account for API testing.

Remaining authentication milestones:

- Set initial password for employee accounts.
- Forgot-password request.
- Password reset.
- Email notifications for password workflows.

Out of current scope:

- Self-registration, email verification, two-factor authentication, and social login.

### Authorization

Status: Partial

Models: Spatie roles and permissions attached to `User`.

Delivered:

- HR and Employee roles.
- Permission-backed policies for Employees, Departments, Shifts, and Holidays.
- HR receives management permissions for the delivered domains.

Remaining:

- Add policies and permissions as Attendance, Corrections, Overtime, Leave, Reports, Notifications, and Manager workflows are implemented.
- Define any manager-specific role and authority model before building the Manager Module.

### Departments

Status: Complete

Model: `Department`.

Delivered for HR:

- Create, list, view, update, and delete departments.
- Search, sorting, pagination, unique name and code validation.

Delivered for employees:

- Department view permission.

Remaining:

- Department-based employee-number generation is deferred to a future version.

### Employees

Status: Partial

Models: `Employee` and its one-to-one `User` relationship.

Delivered for HR:

- Create an employee and associated user account with a temporary password.
- Assign department and optional shift.
- List, view, update, delete, and restore employees.
- Upload and replace identity documents.

Delivered for employees:

- A user account and Employee role are created during onboarding.

Remaining:

- Employee self-service profile view and update endpoints.
- Initial-password workflow so employees can replace their temporary password.
- Employee-number generation.

### Shift Management

Status: Complete

Models: `Shift` and `ShiftDay`.

Delivered for HR:

- Create, list, view, update, and delete shifts.
- Manage the complete Shift Day aggregate inside each Shift request.
- Configure one schedule per weekday, work start time, work end time, and total break duration.
- Assign a shift while creating or updating an employee.
- Seed a reusable Morning Shift for testing.

Delivered for employees:

- Employees may have an assigned shift; no employee shift-management endpoint is exposed.

Remaining:

- Bulk shift assignment.
- Overnight schedule calculation and validation when Attendance Tracking requires it.
- Detailed break periods only if a future requirement needs them.

### Holiday Management

Status: Complete

Model: `Holiday`.

Delivered for HR:

- Create, list, view, update, and delete holidays.
- Store single-day or multi-day holiday ranges.
- Default an omitted end date to the start date.
- Allow overlapping holiday ranges.
- Filter holidays by `month=YYYY-MM`, including ranges that overlap the requested month.
- Import holiday ranges from an Excel workbook.
- Download the version-controlled Excel import template.
- Use `HolidayService::isHoliday()` in later attendance logic.

Delivered for employees:

- No direct holiday endpoint is required in Version 1.

Remaining:

- Attendance Tracking will use holiday coverage when interpreting a working day.
- Holiday attendance must remain separate from overtime approval.

## Attendance Feature Milestones

### 1. Shift Management

Status: Complete

### 2. Holiday Management

Status: Complete

### 3. Attendance Tracking

Status: Partial

Models: `SystemConfiguration`, `Attendance`, and `AttendanceLog`.

Delivered for employees:

- Clock in and clock out with a required private image.
- Use a server-managed action timestamp.
- Record actions through one endpoint using the `attendance.record` permission and require an assigned shift.
- List and view only their own attendance records.
- Receive automatic end-of-day attendance finalization without a manual end-day action.

Delivered for HR:

- Version 1 receives a seeded Office WiFi configuration with a one-minute minimum action interval and zero grace minutes.
- List and view all attendance records, with employee-name, date, status, sorting, and pagination filters.
- Review records that are incomplete or waiting for correction approval.

Delivered rules:

- The first action is clock in.
- Actions alternate between clock in and clock out.
- Consecutive actions are at least one minute apart.
- Working time can be calculated from completed clock-in/clock-out pairs.
- An employee requires an assigned shift before using attendance endpoints.
- Weekend and holiday attendance may be recorded and does not automatically create overtime.

Remaining:

- Re-enable configuration-management endpoints when HR configuration becomes part of the Version 1 workflow.
- Exact request payload for GPS and Office WiFi evidence.
- Working-time calculation and reporting.

### 4. Attendance Corrections

Status: Complete

Models: `AttendanceCorrection` and `AttendanceCorrectionLog`.

Delivered for employees:

- Select an attendance record and submit a note with its complete proposed timeline.
- View only their own correction requests.
- Filter own requests by status or attendance date.

Delivered for HR:

- List and filter correction requests by status, attendance date, employee ID/name, or department.
- Approve or reject pending requests.

Delivered rules:

- Employees do not edit Attendance Logs directly.
- An attendance record has at most one pending correction.
- Proposed timelines must alternate clock-in and clock-out, end with clock-out, and respect the configured interval.
- Pending and rejected corrections do not alter attendance.
- Approval atomically replaces the selected attendance record's logs.

Remaining:

- HR decision notes and employee notifications.

### 5. Overtime

Status: Planned

Models to introduce:

- `Overtime` request and approval data.

Employee workflow:

- Submit overtime requests according to company policy.
- View request status.

HR workflow:

- Review, approve, or reject overtime requests.

Rules already decided:

- Attendance records work performed; overtime determines approved compensation.
- Weekend or holiday attendance does not automatically create overtime.

## Other Planned Domains

### Leave Management

Status: Planned

Expected models and workflows:

- Leave types, leave balances, and leave requests.
- Employees submit and track leave requests.
- HR manages leave policy and approves or rejects requests.
- Leave balances remain decimal days, including half-day values.

### Reports

Status: Planned

Expected HR workflows:

- Attendance, absence, overtime, leave, and department reporting.
- Export requirements, filters, and report ownership must be defined before implementation.

### Notifications

Status: Planned

Expected workflows:

- Authentication emails for password milestones.
- Notifications for attendance corrections, overtime, leave, and HR decisions.
- Delivery channels and notification preferences must be defined before implementation.

### Manager Module

Status: Planned

Expected workflows:

- A manager role with limited visibility over an assigned team or department.
- Team attendance and approval responsibilities.
- Manager scope, hierarchy, and approval authority must be defined before implementation.

## Delivery Order

1. Review Attendance Tracking and Attendance Corrections.
2. Implement Overtime.
3. Review Overtime.
4. Review the complete Attendance feature before merging `feature/attendance` into `develop` with `--no-ff`.
