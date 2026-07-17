# Attendance Design

## Overview

The Attendance module records employee attendance on a daily basis.

Each employee has one attendance record per working day. The attendance record acts as the parent entity, while every clock action is stored as an attendance log.

Attendance supports multiple clock-in and clock-out actions during the same day, allowing employees to take lunch breaks, prayer breaks, or any other temporary leave without limiting the number of attendance intervals.

Working hours are calculated from attendance logs rather than fixed clock-in and clock-out columns.

---

## Attendance Record

- One attendance record is created per employee per working day.
- Attendance belongs to one employee.
- Attendance represents the daily attendance summary.
- Attendance does not store clock-in or clock-out timestamps directly.
- Attendance logs contain all attendance actions.
- Version 1 supports day shifts only.
- If an attendance spans midnight, it belongs to the clock-in date.

---

## Attendance Logs

- Attendance logs belong to one attendance record.
- Each attendance log represents a single attendance action.
- Supported actions are:
  - Clock In
  - Clock Out
- There is no limit on the number of attendance logs per attendance.
- Attendance logs are stored chronologically.
- Working time is calculated by pairing consecutive Clock In and Clock Out actions.

Example:

```text
08:55 Clock In
13:00 Clock Out
14:00 Clock In
18:05 Clock Out
```

Working duration:

```text
08:55 → 13:00
14:00 → 18:05
```

Lunch breaks and other breaks are automatically excluded from the working duration calculation.

---

## Attendance Validation

Every attendance timeline must satisfy the following rules:

- The first action must always be Clock In.
- Actions must alternate between Clock In and Clock Out.
- The final action of the day must always be Clock Out.
- Consecutive attendance actions must be separated by at least one minute.

Validation is applied when:

- Creating attendance logs.
- Submitting attendance correction requests.
- Approving attendance correction requests.

---

## Attendance Status

Attendance status is finalized through scheduled jobs after the working day ends.

Possible statuses:

- Incomplete
- Waiting For Approval
- Completed

Examples:

Incomplete

```text
08:55 Clock In
```

Waiting For Approval

```text
Employee submitted a correction request.
```

Completed

```text
08:55 Clock In
13:00 Clock Out
14:00 Clock In
18:05 Clock Out
```

---

## Attendance Calculation

Working duration is calculated from attendance logs.

The system pairs every Clock In with the following Clock Out.

Only completed attendance intervals contribute to the total working duration.

Breaks are excluded automatically.

No attendance-specific lunch break logic is required.

---

## Attendance Configuration

Attendance functionality depends on system configuration.

The following settings must be configured before attendance endpoints become available:

- Working hours
- Working days
- Attendance methods

Attendance endpoints are protected by middleware that verifies the attendance configuration has been completed.

---

## Attendance Methods

Version 1 supports:

- GPS
- Office WiFi

QR Code attendance is planned for Version 2.

The allowed attendance methods are configured by HR.

---

## Working Days

Working days are configured by HR.

Attendance is expected only on configured working days.

Attendance may still be recorded during weekends and holidays.

Weekend and holiday attendance is handled separately from attendance validation.

---

## Holidays

HR manages holidays.

Holidays may be created:

- Individually.
- Through bulk Excel import.

Attendance during holidays does not automatically become approved overtime.

---

## Overtime

Attendance and overtime are independent concepts.

Attendance records that an employee worked.

Overtime determines whether that work is approved for compensation.

Weekend or holiday attendance does not automatically create overtime.

Employees must submit overtime requests according to company policy.

---

## Attendance Corrections

Employees cannot modify attendance logs directly.

Instead, they submit attendance correction requests.

A correction request may:

- Modify an existing attendance action.
- Add a missing attendance action.

Before approval, the system reconstructs the complete attendance timeline and validates it using the attendance validation rules.

Only approved correction requests update attendance logs.

Rejected requests do not modify attendance.

---

## Scheduled Jobs

The system uses scheduled jobs for attendance maintenance.

Daily scheduled job:

- Finalizes attendance status.
- Marks incomplete attendance.
- Marks completed attendance.
- Updates attendance waiting for HR approval.

Monthly scheduled job:

- Sends reminders to employees for unresolved incomplete attendance requiring correction.

---

## Future Scope

The following features are outside Version 1:

- QR Code attendance.
- Overnight shifts.
- Employee number integration.
- Automatic overtime approval.
- Automatic attendance correction.