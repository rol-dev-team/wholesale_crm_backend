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
        Schema::create('user_supervisor_mappings', function (Blueprint $table) {
            $table->id();
                $table->unsignedBigInteger('user_id')->index();

                // NULL  => All supervisors
                $table->unsignedBigInteger('supervisor_id')->nullable()->index();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_supervisor_mappings');
    }
};
