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

Attendance Log
- Clock-in or clock-out action belonging to one Attendance
- Server-managed `created_at` timestamp and private image path

## Relationships

User (1) ------ (1) Employee

Department (1) ------ (*) Employee

Shift (1) ------ (*) Shift Day

Shift (1) ------ (*) Employee

Employee (1) ------ (*) Attendance

Attendance (1) ------ (*) Attendance Log

## Constraints

- `employees.user_id` is unique and references `users.id`.
- `employees.department_id` is nullable and references `departments.id`.
- `shift_days.shift_id` references `shifts.id` and cascades on deletion.
- `shift_days` has a unique constraint on `(shift_id, day_of_week)`.
- `employees.shift_id` is nullable and references `shifts.id`; assigned shifts cannot be deleted.
- Holiday date ranges are independent records and may overlap.
- `attendances` has a unique constraint on `(employee_id, attendance_date)`.
- `attendance_logs.attendance_id` references `attendances.id` and cascades on deletion.
