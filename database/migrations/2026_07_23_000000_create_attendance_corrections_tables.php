<?php

use App\Enums\AttendanceAction;
use App\Enums\AttendanceCorrectionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->restrictOnDelete();
            $table->date('attendance_date');
            $table->text('note');
            $table->enum('status', AttendanceCorrectionStatus::values())
                ->default(AttendanceCorrectionStatus::PENDING->value);
            $table->timestamps();

            $table->index(['employee_id', 'attendance_date']);
        });

        Schema::create('attendance_correction_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_correction_id')->constrained()->cascadeOnDelete();
            $table->enum('action', AttendanceAction::values());
            $table->timestamp('action_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_correction_logs');
        Schema::dropIfExists('attendance_corrections');
    }
};
