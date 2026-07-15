<?php

namespace App\Enums;

enum Permission: string
{
    // Employee Profile
    case PROFILE_VIEW = 'profile.view';
    case PROFILE_UPDATE = 'profile.update';
    case EMPLOYEES_VIEW = 'employees.view';
    case EMPLOYEES_CREATE = 'employees.create';
    case EMPLOYEES_UPDATE = 'employees.update';
    case EMPLOYEES_DELETE = 'employees.delete';
    case EMPLOYEES_RSTORE = 'employees.restore';

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
