# Git Branching Strategy

## Branches

### main

Contains stable and production-ready code.

### develop

Main integration branch used during development.

### feature/*

Each new feature is developed in its own branch.

Examples:

- feature/authentication
- feature/attendance
- feature/leave-management

## Workflow

develop
    ↓
feature/*
    ↓
develop
    ↓
main

Feature branches are deleted after being merged.

## Merge Strategy

Feature branches must be merged into `develop` using:

```bash
git merge --no-ff feature/<branch-name>
```

This preserves the complete history of each feature.