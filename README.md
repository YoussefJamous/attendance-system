# Attendance System API

A RESTful Attendance Management System built with Laravel 13.

The project is designed following modern software engineering practices with a strong focus on maintainability, clean architecture, and scalability.

---

## Features

- Employee Attendance
- Leave Management
- Attendance Corrections
- Overtime Requests
- Department Management
- Role & Permission Management
- Reporting
- Notifications (Planned)

---

## Tech Stack

- PHP 8.3
- Laravel 13
- MySQL
- Laravel Sanctum
- Spatie Laravel Permission
- Bruno API Client

---

## Project Structure

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

---

## Documentation

Project documentation can be found inside the `docs` directory.

- Architecture
- Business Rules
- Coding Standards
- Database Design
- Development Workflow
- Decision Log
- Roadmap

---

## Git Workflow

The project follows a feature branch workflow.

```
main
    │
develop
    │
feature/*
```

Feature branches are merged into `develop` using:

```bash
git merge --no-ff feature/<branch-name>
```

---

## Commit Convention

The project follows Conventional Commits.

| Type | Description |
|------|-------------|
| feat | New feature |
| fix | Bug fix |
| docs | Documentation |
| refactor | Code improvements without changing behavior |
| test | Tests |
| chore | Project maintenance |
| wip | Work in progress |

Examples

```text
feat: implement attendance clock-in

fix: prevent duplicate attendance

docs: update business rules

chore: install Laravel Sanctum
```

---

## License

This project is intended for educational and portfolio purposes.