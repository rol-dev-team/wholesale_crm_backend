<?php

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
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kam_id')->index();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('activity_type_id')->index();
            $table->unsignedBigInteger('action_by')->index();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('meeting_location')->nullable();

            $table->dateTime('activity_schedule')->nullable();

            $table->enum('status', ['upcoming', 'overdue', 'completed', 'cancelled'])
                ->default('upcoming')
                ->index();
            $table->enum('action_type', [
                    'inserted',
                    'edited',
                    'note_added',
                    'completed',
                    'cancelled',
                    'status_changed',
                    'rescheduled'
                ])->index();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};
