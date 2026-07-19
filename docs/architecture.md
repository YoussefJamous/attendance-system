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
- Holiday defines a date range when normal work is not expected.
- System Configuration defines global attendance settings managed by HR.
- Attendance defines an employee's daily attendance summary.
- Attendance Log defines a clock-in or clock-out action, timestamped by its server-managed `created_at` value, with image evidence.
- Attendance Correction and Overtime will be added in later subdomains.
