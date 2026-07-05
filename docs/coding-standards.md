# Coding Standards

## General

- Follow Laravel conventions whenever possible.
- Prefer readability over clever code.
- Avoid premature abstractions (YAGNI).
- Keep classes focused on a single responsibility.

## Controllers

- Coordinate requests only.
- Do not contain business logic.

## Services

- Contain business logic.
- Do not return HTTP responses.

## Models

- Represent database entities.
- Contain relationships and query scopes only.

## Resources

- Transform API responses.

## Enums

- Use PHP Enums.
- Database ENUM values should reference the Enum class whenever possible.

## Database

- All primary keys use UUID.
- The primary key column must be named `id`.
- Foreign keys referencing UUIDs must use `foreignUuid()`.
- UUID generation must use Laravel's built-in `HasUuids` trait.