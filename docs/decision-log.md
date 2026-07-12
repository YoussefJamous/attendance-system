# Decision Log

This document records important architectural decisions made throughout the project.

The purpose of this document is to avoid re-discussing previously resolved topics and to explain the reasoning behind major technical decisions.

---

# ADR-001

## Title

Service-Based Architecture

## Status

Accepted

## Decision

Business logic will be implemented inside Service classes.

Controllers are responsible only for coordinating requests and responses.

## Reason

Keeping controllers thin improves readability, maintainability, and testability.

## Alternatives Considered

- Business logic inside Controllers
- Repository Pattern

## Impact

All business operations should be implemented inside the appropriate Service class.

---

# ADR-002

## Title

API Versioning

## Status

Accepted

## Decision

All API endpoints will be versioned.

The first version will be:

/api/v1

Controllers will be placed inside:

App\Http\Controllers\API\V1

## Reason

API versioning allows future changes without breaking existing clients.

## Alternatives Considered

- Unversioned API

## Impact

Every new endpoint must belong to an API version.

---

# ADR-003

## Title

Git Branching Strategy

## Status

Accepted

## Decision

The project follows the following branching strategy:

main

↓

develop

↓

feature/*

Every feature is implemented in its own branch.

## Reason

Provides better organization, isolated development, and cleaner Git history.

## Alternatives Considered

- Development directly on develop
- GitHub Flow

## Impact

No feature should be developed directly on develop.

---

# ADR-004

## Title

Merge Strategy

## Status

Accepted

## Decision

Feature branches must be merged using:

git merge --no-ff

## Reason

Preserves the history of each feature branch and produces a clearer Git graph.

## Alternatives Considered

Fast-forward merge.

## Impact

Every completed feature should appear as an independent merge in Git history.

---

# ADR-005

## Title

API Testing Tool

## Status

Accepted

## Decision

Bruno will be used for API testing.

Collections will be stored inside the repository.

## Reason

Bruno stores collections as plain text files, making them easy to version using Git.

## Alternatives Considered

- Postman
- Insomnia

## Impact

API collections become part of the project source code.

---

# ADR-006

## Title

Response Architecture

## Status

Accepted

## Decision

API responses must be returned using Laravel Resources.

Controllers must not return Eloquent models directly.

## Reason

Resources provide a consistent API response format.

## Alternatives Considered

Returning Models directly.

## Impact

Every API endpoint should have a corresponding Resource.

---

# ADR-007

## Title

Validation Strategy

## Status

Accepted

## Decision

Input validation will be implemented using Form Requests.

Validation must not be performed inside Controllers.

## Reason

Separates validation from business logic.

## Alternatives Considered

Validation inside Controllers.

## Impact

Every endpoint that accepts input should have its own Form Request.

---

# ADR-008

## Title

Authorization Strategy

## Status

Accepted

## Decision

Authorization will be handled using Laravel Policies.

## Reason

Policies provide centralized authorization rules.

## Alternatives Considered

Authorization inside Controllers or Services.

## Impact

Controllers should call Policies when authorization is required.

---

# ADR-009

## Title

Role and Permission Management

## Status

Accepted

## Decision

The project will use the spatie/laravel-permission package.

## Reason

It is the Laravel community standard and provides a flexible role and permission system.

## Alternatives Considered

Building a custom role system.

## Impact

Roles and permissions will not be implemented manually.

---

# ADR-010

## Title

Attendance Status Representation

## Status

Accepted

## Decision

Attendance status values will be represented using:

- PHP Enums
- Database ENUM columns

Database ENUM values will be generated from the PHP Enum class.

Example:

AttendanceStatus::values()

## Reason

Keeps PHP and database values synchronized while enforcing valid database values.

## Alternatives Considered

VARCHAR columns with PHP Enums only.

## Impact

Whenever a new status is added, both the Enum and a database migration must be updated.

---

# ADR-011

## Title

Leave Balance Unit

## Status

Accepted

## Decision

Leave balances will be stored as DECIMAL values representing days.

Example:

14.00

13.50

12.00

## Reason

The company currently measures leave in days rather than hours.

Using DECIMAL allows half-day leave while keeping the implementation simple.

## Alternatives Considered

- Hours
- Minutes

## Impact

If the company later adopts hourly leave, the migration effort will be manageable.

# Decision-012

## Title

UUID Strategy

## Status

Accepted

## Decision

The project will use UUIDs as the primary key for all entities.
UUIDs will be generated using Laravel's built-in UUID support.

## Reason

Using Laravel's built-in implementation follows the framework's conventions, minimizes custom code, and provides globally unique identifiers suitable for distributed systems.

---

# ADR-013

## Title

API Exception Response Format

## Status

Accepted

## Decision

API exceptions will be rendered using the same standard error response structure used by API controllers.

The response must include:

- success
- message
- errors, when validation or field-level details are available

## Reason

Clients should receive a predictable JSON structure for both expected controller responses and framework-level exceptions.

## Alternatives Considered

- Use Laravel's default exception response format.
- Create a custom exception handler abstraction.

## Impact

Exception rendering should stay inside Laravel's exception configuration unless a future requirement creates a real need for a separate abstraction.

---

# ADR-014

## Title

API Authentication Strategy

## Status

Accepted

## Decision

API authentication will use Laravel Sanctum personal access tokens.

Authentication endpoints will be implemented through thin API controllers, Form Requests for validation, AuthService for authentication logic, and Resources for user response data.

## Reason

Sanctum is Laravel's first-party solution for API token authentication and is already installed in the project. Keeping authentication logic inside AuthService follows the existing service-based architecture without adding extra abstractions.

## Alternatives Considered

- Laravel session authentication for API requests.
- Custom token implementation.
- JWT package.

## Impact

Protected API routes must use the auth:sanctum middleware. Authentication responses must continue using the standard API response format.

# ADR-015

## Title

Separate Employee Data from User Accounts

## Status

Accepted

## Decision

The project will separate authentication data from employee information.

The `users` table will contain only data related to authentication and authorization, such as:

- id
- email
- password
- authentication metadata

Employee-specific information will be stored in a dedicated `employees` table.

A user account will be associated with a single employee record.

## Reason

Authentication and employee management represent different business concerns.

Separating them provides a clearer domain model and allows each table to evolve independently.

Examples include:

- Employee profile information
- Employment details
- Department assignment
- Attendance
- Leave management

These belong to the employee domain rather than the authentication domain.

This separation also keeps the authentication model small and focused while reducing future coupling between security-related data and business data.

## Alternatives Considered

Store all employee information in the `users` table.

This approach was rejected because the table would gradually become responsible for both authentication and employee management, violating the single responsibility principle at the domain level.

## Impact

- `User` becomes the authentication identity.
- `Employee` becomes the business entity.
- Authentication continues to work through the `User` model.
- Business modules (attendance, leave requests, departments, etc.) relate to `Employee` instead of `User`.
