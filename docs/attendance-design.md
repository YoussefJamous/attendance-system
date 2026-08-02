# Attendance Design

## Overview

The Attendance module records employee attendance on a daily basis.

Each employee has one attendance record per working day. The attendance record acts as the parent entity, while every clock action is stored as an attendance log.

Attendance supports multiple clock-in and clock-out actions during the same day, allowing employees to take lunch breaks, prayer breaks, or any other temporary leave without limiting the number of attendance intervals.

Employees record every direct action through one attendance endpoint. The service derives the next action from the latest log, so a new day begins with clock-in and each later request alternates clock-out and clock-in.

Working hours are calculated from attendance logs rather than fixed clock-in and clock-out columns.

Employees may be created before a shift is assigned. A shift assignment will be required before an employee can use attendance endpoints when Attendance Tracking is implemented.

---

## Attendance Record

- One attendance record is created per employee per working day.
- Attendance belongs to one employee.
- Attendance represents the daily attendance summary.
- Attendance does not store clock-in or clock-out timestamps directly.
- Attendance logs contain all attendance actions.
- A clock action uses its server-managed `created_at` timestamp.
- Every direct clock action requires an image as attendance evidence.
- Actions must alternate: clock-in, then clock-out, with later intervals following the same order.
- Employees require the `attendance.record` permission to record an action.

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

- Attendance method
- Minimum time between actions
- Grace minutes

Working hours and working days are configured through Shift Days. Each Shift Day defines one weekday's start time, end time, and total break duration.

HR creates one global attendance configuration before attendance endpoints can be used. Attendance endpoints verify that the employee has an assigned shift before allowing attendance actions.

The configured attendance method is stored for future enforcement. Version 1 does not yet validate GPS, Office WiFi, or other method evidence.

---

## Attendance Methods

Version 1 supports:

- GPS
- Office WiFi

QR Code attendance is planned for Version 2.

The allowed attendance methods are configured by HR.

---

## Working Days

Working days are configured by HR through Shift Days.

Attendance is expected only on configured working days.

Attendance may still be recorded during weekends and holidays.

Weekend and holiday attendance is handled separately from attendance validation.

---

## Holidays

HR manages holidays.

Holidays may be created:

- Individually.
- Through bulk Excel import.

Each holiday stores a name, optional description, start date, and end date. When the end date is omitted, it defaults to the start date. Holiday ranges may overlap.

The import template uses the columns: `name`, `description`, `start_date`, and `end_date`.

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

A correction request contains a note and the employee's complete proposed timeline for one attendance date. It may add, remove, or modify actions by replacing the proposed array, even when some proposed actions match the existing log.

Correction actions contain an action and requested timestamp. They do not require images.

The submitted timeline is validated using the attendance validation rules, including the configured minimum interval. A pending correction does not modify the existing attendance logs.

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
- Overnight shift calculations and validation.
- Employee number integration.
- Automatic overtime approval.
- Automatic attendance correction.
