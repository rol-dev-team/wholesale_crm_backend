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
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->date('target_month'); // e.g. 2026-01-01
            $table->string('division')->index();

            $table->unsignedBigInteger('supervisor_id')->nullable()->index();
            $table->unsignedBigInteger('kam_id')->index();

            $table->decimal('amount', 15, 2);

            $table->unsignedBigInteger('posted_by')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
