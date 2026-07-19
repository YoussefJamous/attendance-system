<?php

namespace App\Enums;

enum Permission: string
{
    // Employee Profile
    case PROFILE_VIEW = 'profile.view';
    case PROFILE_UPDATE = 'profile.update';

    // Employee
    case EMPLOYEES_VIEW = 'employees.view';
    case EMPLOYEES_CREATE = 'employees.create';
    case EMPLOYEES_UPDATE = 'employees.update';
    case EMPLOYEES_DELETE = 'employees.delete';
    case EMPLOYEES_RSTORE = 'employees.restore';

    // Department
    case DEPARTMENTS_VIEW = 'departments.view';
    case DEPARTMENTS_CREATE = 'departments.create';
    case DEPARTMENTS_UPDATE = 'departments.update';
    case DEPARTMENTS_DELETE = 'departments.delete';

    // Shift
    case SHIFTS_VIEW = 'shifts.view';
    case SHIFTS_CREATE = 'shifts.create';
    case SHIFTS_UPDATE = 'shifts.update';
    case SHIFTS_DELETE = 'shifts.delete';

    // Holiday
    case HOLIDAYS_VIEW = 'holidays.view';
    case HOLIDAYS_CREATE = 'holidays.create';
    case HOLIDAYS_UPDATE = 'holidays.update';
    case HOLIDAYS_DELETE = 'holidays.delete';

    // Attendance
    /* case ATTENDANCE_VIEW = 'attendance.view';
    case ATTENDANCE_CHECK_IN = 'attendance.checkin';
    case ATTENDANCE_CHECK_OUT = 'attendance.checkout';

    // Leave
    case LEAVE_VIEW = 'leave.view';
    case LEAVE_CREATE = 'leave.create';
    case LEAVE_APPROVE = 'leave.approve';

    // Overtime
    case OVERTIME_VIEW = 'overtime.view';
    case OVERTIME_CREATE = 'overtime.create';
    case OVERTIME_APPROVE = 'overtime.approve'; */
}
