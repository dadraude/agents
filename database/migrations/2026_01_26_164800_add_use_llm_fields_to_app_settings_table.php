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
        Schema::table('app_settings', function (Blueprint $table) {
            $table->boolean('use_llm_interpreter')->nullable()->after('active_linear_writer');
            $table->boolean('use_llm_classifier')->nullable()->after('use_llm_interpreter');
            $table->boolean('use_llm_validator')->nullable()->after('use_llm_classifier');
            $table->boolean('use_llm_prioritizer')->nullable()->after('use_llm_validator');
            $table->boolean('use_llm_decision_maker')->nullable()->after('use_llm_prioritizer');
            $table->boolean('use_llm_linear_writer')->nullable()->after('use_llm_decision_maker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn([
                'use_llm_interpreter',
                'use_llm_classifier',
                'use_llm_validator',
                'use_llm_prioritizer',
                'use_llm_decision_maker',
                'use_llm_linear_writer',
            ]);
        });
    }
};
