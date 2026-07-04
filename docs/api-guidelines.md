# API Guidelines

## Base URL

/api/v1

## Response Format

Successful responses

```json
{
    "success": true,
    "message": "Operation completed successfully.",
    "data": {}
}
```

Failed responses

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```