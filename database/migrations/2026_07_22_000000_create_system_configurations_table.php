<?php

use App\Enums\AttendanceMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('attendance_method', AttendanceMethod::values());
            $table->unsignedInteger('minimum_action_interval_minutes')->default(1);
            $table->unsignedInteger('grace_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};
