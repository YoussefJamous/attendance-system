# Architecture

The project follows a layered architecture.

```
Controller
│
├── Web Controller
│
└── ApiController
        ↓
Form Request
        ↓
Service
        ↓
Resource
```

## Principles

- Controllers coordinate requests.
- Services contain business logic.
- Models represent data.
- Resources transform responses.
- Authorization is handled using Policies.
- Validation is handled using Form Requests.
- API exceptions are rendered using the standard API error response format.

The project favors Laravel conventions over custom abstractions.

## Attendance Domain

- Shift defines an employee schedule.
- Shift Day defines the schedule for one weekday within a shift.
- Holiday, Attendance, Attendance Log, Attendance Correction, and Overtime will be added in later subdomains.
