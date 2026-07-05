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

The project favors Laravel conventions over custom abstractions.