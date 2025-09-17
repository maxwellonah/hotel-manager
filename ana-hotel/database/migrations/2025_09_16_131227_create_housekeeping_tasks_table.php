<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHousekeepingTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();

            // Room reference
            $table->foreignId('room_id')
                ->constrained()
                ->onDelete('cascade');

            // User assignment (explicit foreign key definitions)
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();

            $table->foreign('assigned_to')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('assigned_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // Task info
            $table->enum('task_type', ['cleaning', 'inspection', 'maintenance', 'deep_cleaning', 'other']);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
}
