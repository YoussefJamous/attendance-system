# Project Architecture

## Overview

Attendance System is a RESTful API built with Laravel 13.

The project follows a layered architecture where each layer has a single responsibility.

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

## Architecture Principles

- Controllers coordinate requests only.
- Validation is handled by Form Requests.
- Business logic belongs inside Services.
- Authorization is handled by Policies.
- Models represent database entities.
- API responses are returned using Resources.
- API endpoints are versioned (`/api/v1`).

## Future Considerations

The architecture should remain scalable enough to support:

- Manager module
- Notifications
- Payroll integration
- Mobile application