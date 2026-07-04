# Coding Standards

This document defines the project's coding conventions and architectural rules.

---

# General Principles

- Prefer simplicity over unnecessary abstraction.
- Follow Laravel conventions whenever possible.
- Avoid premature optimization.
- Keep the code readable.
- Every class should have a single responsibility.

---

# Controllers

Controllers should:

- Coordinate requests.
- Call Services.
- Return Resources.
- Perform authorization.

Controllers should NOT:

- Contain business logic.
- Perform validation.
- Execute complex database queries.

---

# Form Requests

Every endpoint that accepts user input should use a dedicated Form Request.

Validation belongs inside Form Requests.

---

# Services

Services contain business logic.

Services should:

- Perform business operations.
- Interact with Models.
- Throw business exceptions when necessary.

Services should NOT:

- Return HTTP responses.
- Read data directly from the Request object.
- Perform authorization.

---

# Models

Models represent database entities.

Models may contain:

- Relationships
- Query scopes
- Attribute casting
- Small helper methods

Models should NOT contain business workflows.

---

# Resources

Resources are responsible for transforming Models into API responses.

Never return Eloquent models directly.

---

# Policies

Policies are responsible for authorization.

Authorization should not be duplicated inside Services.

---

# Validation

Validation should only exist inside Form Requests.

---

# Enums

Application constants should be represented using PHP Enums.

Whenever possible, database ENUM values should be generated from the PHP Enum.

Example:

```php
$table->enum(
    'status',
    AttendanceStatus::values()
);
```

---

# API Responses

Responses should remain consistent across the application.

Every response should contain:

- success
- message
- data

Validation responses may also contain:

- errors

---

# Naming Conventions

Controllers

```
AttendanceController
```

Services

```
AttendanceService
```

Requests

```
StoreAttendanceRequest
```

Resources

```
AttendanceResource
```

Policies

```
AttendancePolicy
```

Enums

```
AttendanceStatus
```

---

# Architecture Rules

The project follows this architecture.

```
Controller
        ↓
Form Request
        ↓
Service
        ↓
Model
        ↓
Resource
```

Business logic should never bypass this flow without a valid reason.

---

# YAGNI Principle

The project follows the YAGNI principle.

Do not introduce additional architectural patterns until they solve a real problem.

Examples:

- No Repository Pattern unless justified.
- No Action classes until Services become too large.
- No Traits unless code duplication exists.

The simplest solution that satisfies the requirements should be preferred.