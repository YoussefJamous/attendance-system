# Database Design

This document will contain:

- Entity Relationship Diagram (ERD)
- Naming conventions
- Relationships
- Constraints
- Database decisions


# Database Conventions

## Primary Keys

All tables use UUID as the primary key.

## Foreign Keys

Relationships between tables should use UUID foreign keys.

## User and Employee

The system separates authentication from employee information.

User
- Authentication identity
- Email
- Password
- Roles and permissions

Employee
- Business entity
- Personal information
- Department
- Shift
- Attendance
- Leave requests

Relationship

User (1) ------ (1) Employee

## Shifts

Shift
- UUID primary key
- Name, optional description, and active status

Shift Day
- UUID primary key
- Belongs to one Shift
- Stores the weekday, work start time, work end time, overnight indicator, and total break duration

Relationships

Shift (1) ------ (*) Shift Day

Shift (1) ------ (*) Employee

Constraints

- `shift_days.shift_id` references `shifts.id` and cascades on deletion.
- `shift_days` has a unique constraint on `(shift_id, day_of_week)`.
- `employees.shift_id` references `shifts.id`; assigned shifts cannot be deleted.
