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
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->string('supervisor_contact', 20)->nullable();
            $table->string('parent_wa_contact', 20)->nullable();
            $table->text('current_activities')->nullable();
            $table->string('social_media_instagram', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'supervisor_contact',
                'parent_wa_contact',
                'current_activities',
                'social_media_instagram'
            ]);
        });
    }
};
