<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Employee;
use App\Models\User;
use App\Pipelines\Employee\GenderFilterPipeline;
use App\Pipelines\Employee\SearchPipeline;
use App\Pipelines\Employee\SortPipeline;
use App\Pipelines\Employee\StatusFilterPipeline;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class EmployeeService
{
    public function index(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = app(Pipeline::class)
            ->send(Employee::query()->with('user'))
            ->through([
                new SearchPipeline($filters['search'] ?? null),
                new StatusFilterPipeline($filters['status'] ?? null),
                new GenderFilterPipeline($filters['gender'] ?? null),
                new SortPipeline($filters['sort_by'] ?? 'name', $filters['direction'] ?? 'asc',),
            ])
            ->thenReturn();

        return $query->paginate($perPage)->appends($filters);
    }

    public function store(array $data, ?UploadedFile $identityDocument = null): array
    {
        // Wrap the operation in a database transaction.
        return DB::transaction(function () use ($data, $identityDocument) {
            // Create the associated user account and assign the employee role.
            $temporaryPassword = Str::password();
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
            ]);
            $user->assignRole(Role::EMPLOYEE->value);

            // Store the identity document if one was uploaded.
            $documentPath = null;
            if ($identityDocument) {
                $documentPath = $identityDocument->store('employees/identity-documents', 'public');
            }

            // Create the submitted employee
            $employeeData = [
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'hire_date' => $data['hire_date'],
                'gender' => $data['gender'],
                'identity_document_path' => $documentPath,
                ...isset($data['status']) ? ['status' => $data['status']] : [],
            ];
            $employee = Employee::create($employeeData)->refresh()->load('user');


            // Return the employee with a temporary password for the assoiated user account
            return ['employee' => $employee, 'temporary_password' => $temporaryPassword,];
        });
    }

    public function update(Employee $employee, array $data, ?UploadedFile $identityDocument = null): Employee
    {
        // Wrap the operation in a database transaction.
        return DB::transaction(function () use ($employee, $data, $identityDocument) {
            // Update Update the associated user email if provided.
            if (array_key_exists('email', $data)) {
                $employee->user()->update([
                    'email' => $data['email'],
                ]);
            }

            // Replace the identity document if a new one was uploaded.
            if ($identityDocument) {
                if ($employee->identity_document_path) {
                    Storage::disk('public')->delete($employee->identity_document_path);
                }
                $data['identity_document_path'] = $identityDocument->store(
                    'employees/identity-documents',
                    'public'
                );
            }

            // Remove fields that don't belong to the employees table.
            unset($data['email'], $data['identity_document']);

            // Update the employee with only the submitted fields.
            $employee->update($data);

            return $employee->refresh()->load('user');
        });
    }

    public function show(Employee $employee): Employee
    {
        return $employee->loadMissing('user');
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            $employee->user()->delete();
            $employee->delete();
        });
    }

    public function restore(Employee $employee): Employee
    {
        DB::transaction(function () use ($employee) {
            $employee->restore();
            $employee->user()->withTrashed()->restore();
        });

        return $employee->refresh()->load('user');
    }
}
