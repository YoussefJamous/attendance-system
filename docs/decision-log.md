# Decision Log

This register contains only accepted decisions that affect future design or implementation. Detailed historical discussion remains available in Git history.

## Architecture

| Decision | Accepted choice |
| --- | --- |
| Application structure | Controllers coordinate, Form Requests validate, Services hold business logic, Resources shape API responses, and Policies authorize actions. |
| API contract | API routes are versioned under `/api/v1` and use the standard success and error response formats. |
| Identifiers | Domain tables use UUID primary keys and UUID foreign keys. |
| Authentication | Laravel Sanctum personal access tokens authenticate API requests. |
| Authorization | Spatie roles and permissions are enforced through Laravel Policies. |
| User data | `User` owns authentication data; `Employee` owns employment data in a one-to-one relationship. |

## Attendance Domain

| Decision | Accepted choice |
| --- | --- |
| Shift schedules | A Shift owns one or more Shift Days. Each weekday has its own work times and total break duration. |
| Overnight shifts | Version 1 stores overnight-capable times but does not calculate or validate overnight work. |
| Holidays | Holidays are named date ranges, may overlap, and support Excel import through a version-controlled template. |
| Global configuration | HR manages one system configuration with one attendance method, a minimum action interval, and grace minutes. Method-specific evidence validation is deferred. |
| Direct attendance actions | `POST /api/v1/attendance/actions` uses `attendance.record`. The server derives the first action as `clock_in` and alternates later actions from the latest log. |
| Action evidence | Direct actions use server-managed `created_at` timestamps and require private image evidence. |
| Corrections | A correction targets one existing Attendance record and stores a note plus the complete proposed timeline separately from Attendance Logs. |
| Correction approval | Only an HR-approved correction replaces the selected record's logs. Pending and rejected corrections leave the record unchanged. |
| Correction listing | The service establishes HR-all or employee-own scope before reusable status, attendance-date, employee, department, and sorting filters run. |

## Deferred Decisions

- GPS, Office WiFi, and other attendance-method evidence rules.
- Overnight attendance calculation.
- Attendance-status finalization and working-time calculation jobs.
- HR decision notes and correction notifications.
