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
            $table->string('supervisor_name', 255)->nullable()->after('supervisor_contact');
            $table->string('parent_name', 255)->nullable()->after('parent_wa_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->dropColumn(['supervisor_name', 'parent_name']);
        });
    }
};
