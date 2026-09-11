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
        Schema::table('intern_extras', function (Blueprint $table) {
            $table->timestamp('rekomendasi_granted_at')->nullable()->after('rekomendasi_url');
            $table->timestamp('alumni_group_granted_at')->nullable()->after('alumni_group_label');
            $table->timestamp('job_info_granted_at')->nullable()->after('job_info_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_extras', function (Blueprint $table) {
            $table->dropColumn(['rekomendasi_granted_at', 'alumni_group_granted_at', 'job_info_granted_at']);
        });
    }
};
