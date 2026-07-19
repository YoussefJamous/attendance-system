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

## Relationships

User (1) ------ (1) Employee

Department (1) ------ (*) Employee

Shift (1) ------ (*) Shift Day

Shift (1) ------ (*) Employee

## Constraints

- `employees.user_id` is unique and references `users.id`.
- `employees.department_id` is nullable and references `departments.id`.
- `shift_days.shift_id` references `shifts.id` and cascades on deletion.
- `shift_days` has a unique constraint on `(shift_id, day_of_week)`.
- `employees.shift_id` is nullable and references `shifts.id`; assigned shifts cannot be deleted.
- Holiday date ranges are independent records and may overlap.
