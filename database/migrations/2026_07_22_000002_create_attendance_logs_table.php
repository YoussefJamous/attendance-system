<?php

use App\Enums\AttendanceAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_id')->constrained()->cascadeOnDelete();
            $table->enum('action', AttendanceAction::values());
            $table->string('image_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
