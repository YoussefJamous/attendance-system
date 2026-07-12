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
- Attendance
- Leave requests

Relationship

User (1) ------ (1) Employee
