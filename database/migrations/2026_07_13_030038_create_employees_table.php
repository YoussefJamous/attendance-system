<?php

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable()->unique();
            $table->text('address')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('identity_document_path')->nullable();
            $table->enum('status', EmployeeStatus::values())->default(EmployeeStatus::ACTIVE->value);
            $table->enum('gender', Gender::values())->default(Gender::MALE->value);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
