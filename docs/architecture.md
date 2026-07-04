# Architecture

The project follows a layered architecture.

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

## Principles

- Controllers coordinate requests.
- Services contain business logic.
- Models represent data.
- Resources transform responses.
- Authorization is handled using Policies.
- Validation is handled using Form Requests.

The project favors Laravel conventions over custom abstractions.