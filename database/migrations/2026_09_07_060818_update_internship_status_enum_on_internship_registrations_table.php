<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN internship_status ENUM('new', 'waiting', 'active', 'pending', 'completed', 'exited', 'accepted', 'rejected') NOT NULL DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN internship_status ENUM('new', 'waiting', 'active', 'pending', 'completed', 'exited') NOT NULL DEFAULT 'new'");
    }
};
