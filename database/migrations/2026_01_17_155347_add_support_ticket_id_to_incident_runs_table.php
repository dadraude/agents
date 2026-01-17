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
        Schema::table('incident_runs', function (Blueprint $table) {
            $table->string('support_ticket_id')->nullable()->after('id');
            $table->foreign('support_ticket_id')
                ->references('id')
                ->on('support_tickets')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_runs', function (Blueprint $table) {
            $table->dropForeign(['support_ticket_id']);
            $table->dropColumn('support_ticket_id');
        });
    }
};
