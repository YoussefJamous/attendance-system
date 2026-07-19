<?php

use App\Enums\DayOfWeek;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shift_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shift_id')->constrained()->cascadeOnDelete();
            $table->enum('day_of_week', DayOfWeek::cases())->comment('Weekday for this shift schedule.');
            $table->time('work_start_time');
            $table->time('work_end_time');
            $table->boolean('ends_next_day')->default(false);
            $table->unsignedInteger('break_duration_minutes')->default(0);
            $table->timestamps();

            $table->unique(['shift_id', 'day_of_week']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignUuid('shift_id')->after('department_id')->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
        });

        Schema::dropIfExists('shift_days');
        Schema::dropIfExists('shifts');
    }
};
