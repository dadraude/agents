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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('active_interpreter')->default(true);
            $table->boolean('active_classifier')->default(true);
            $table->boolean('active_validator')->default(true);
            $table->boolean('active_prioritizer')->default(true);
            $table->boolean('active_decision_maker')->default(true);
            $table->boolean('active_linear_writer')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
