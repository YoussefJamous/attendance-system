# Custom Commands

## `attendance:finalize`

Finalizes attendance records for one calendar day. The scheduled job runs it once at `23:59` in `APP_TIMEZONE`; it is global to the server and does not run separately for each employee.

```bash
php artisan attendance:finalize
php artisan attendance:finalize 2026-07-20
```

The command processes only `in_progress` records for the selected date:

- A record whose latest log is `clock_out` becomes `completed`.
- A record whose latest log is `clock_in`, or which has no logs, becomes `incomplete`.
- `waiting_for_approval` records are not changed until HR approves or rejects their correction request.

Laravel's scheduler must be invoked by the server every minute:

```cron
* * * * * cd /path/to/attendance-system && php artisan schedule:run >> /dev/null 2>&1
```

Set `APP_TIMEZONE` to the organization's timezone before deploying so that `23:59` represents the intended end of day.
