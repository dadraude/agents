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
        Schema::create('incident_runs', function (Blueprint $table) {
            $table->id();
            $table->text('input_text');
            $table->json('state_json')->nullable();
            $table->json('trace_json')->nullable();
            $table->string('status')->default('processed'); // processed | needs_more_info | escalated | failed
            $table->string('linear_issue_id')->nullable();
            $table->string('linear_issue_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_runs');
    }
};
