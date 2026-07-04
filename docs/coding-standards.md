# Coding Standards

## Controllers

- Controllers should remain thin.
- Controllers coordinate requests only.
- Controllers should not contain business logic.

## Form Requests

- Every endpoint that accepts input should use a Form Request.
- Validation must not be performed inside controllers.

## Services

- Business logic belongs inside Services.
- Services must not return HTTP responses.

## Resources

- API responses should always use Resources.
- Never return Eloquent models directly.

## Policies

- Authorization belongs inside Policies.
- Services should not perform authorization checks.

## Models

- Models represent database entities.
- Keep models focused on relationships, scopes, and small helper methods.