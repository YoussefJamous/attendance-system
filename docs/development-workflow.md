# Development Workflow

This document defines the standard development process for the Attendance System project.

---

# Development Lifecycle

Every feature should follow the same lifecycle.

```
Analyze
    ↓
Decide
    ↓
Document
    ↓
Implement
    ↓
Test
    ↓
Self Review
    ↓
Merge
```

The goal is to ensure every feature is properly designed before implementation.

---

# Feature Development Workflow

## Step 1

Switch to the latest `develop` branch.

```bash
git checkout develop
git pull origin develop
```

---

## Step 2

Create a feature branch.

Example:

```bash
git checkout -b feature/authentication
```

---

## Step 3

Analyze the feature.

Before writing code:

- Understand the business requirements.
- Identify affected modules.
- Review existing documentation.
- Decide whether a new architectural decision is required.

---

## Step 4

Update documentation (if required).

If the feature introduces a new architectural decision:

- Update `decision-log.md`
- Update any related documentation

Documentation is part of the feature, not a separate task.

---

## Step 5

Implement the feature.

Follow the project's coding standards.

---

## Step 6

Test the feature.

API endpoints must be tested using Bruno before merging.

---

## Step 7

Self Review

Before merging ask:

- Does the code follow the coding standards?
- Are controllers still thin?
- Is business logic inside Services?
- Was the API tested?
- Was documentation updated?
- Are there unnecessary abstractions?

---

## Step 8

Commit changes.

Example:

```bash
git commit -m "feat: implement login endpoint"
```

---

## Step 9

Push the feature branch.

```bash
git push -u origin feature/authentication
```

---

## Step 10

Merge into `develop`.

```bash
git checkout develop

git pull origin develop

git merge --no-ff feature/authentication

git push origin develop
```

---

## Step 11

Delete the feature branch.

```bash
git branch -d feature/authentication

git push origin --delete feature/authentication
```

---

# Merging into Main

Only stable milestones should be merged into `main`.

Example milestones:

- Authentication
- Employee Management
- Attendance Module