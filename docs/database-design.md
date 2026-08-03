# Database Design

# Database Conventions

## Primary Keys

All tables use UUID as the primary key.

## Foreign Keys

Relationships between tables should use UUID foreign keys.

## Core Entities

The system separates authentication from employee information.

User
- Authentication identity
- Email
- Password
- Roles and permissions

Employee
- Business entity
- Personal information
- Optional department assignment
- Optional shift assignment
- Attendance
- Leave requests

Department
- Organizational unit for employees

Shift
- Reusable employee work schedule
- Name, optional description, and active status

Shift Day
- One weekday schedule within a Shift
- Work start time, work end time, and total break duration

Holiday
- Named date range when normal work is not expected
- Optional description, start date, and end date

System Configuration
- Global attendance method, minimum action interval, and grace minutes

Attendance
- Daily attendance summary for one employee
- Attendance date and status
- Employee relationship supports HR and employee-scoped attendance listing

Attendance Log
- Clock-in or clock-out action belonging to one Attendance
- Server-managed `created_at` timestamp and nullable private image path

Attendance Correction
- Proposed complete timeline for one Attendance record
- Note and pending, approved, or rejected status

Attendance Correction Log
- Proposed clock-in or clock-out action belonging to one Attendance Correction
- Requested action timestamp

## Relationships

User (1) ------ (1) Employee

Department (1) ------ (*) Employee

Shift (1) ------ (*) Shift Day

Shift (1) ------ (*) Employee

Employee (1) ------ (*) Attendance

Attendance (1) ------ (*) Attendance Log

Attendance (1) ------ (*) Attendance Correction

Attendance Correction (1) ------ (*) Attendance Correction Log

## Constraints

- `employees.user_id` is unique and references `users.id`.
- `employees.department_id` is nullable and references `departments.id`.
- `shift_days.shift_id` references `shifts.id` and cascades on deletion.
- `shift_days` has a unique constraint on `(shift_id, day_of_week)`.
- `employees.shift_id` is nullable and references `shifts.id`; assigned shifts cannot be deleted.
- Holiday date ranges are independent records and may overlap.
- `attendances` has a unique constraint on `(employee_id, attendance_date)`.
- `attendance_logs.attendance_id` references `attendances.id` and cascades on deletion.
- `attendance_corrections.attendance_id` references `attendances.id`.
- `attendance_corrections.attendance_id` is indexed for correction lookup and listing filters.
- `attendance_correction_logs.attendance_correction_id` references `attendance_corrections.id` and cascades on deletion.
