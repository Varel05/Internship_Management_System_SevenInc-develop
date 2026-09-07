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
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN boarding_info ENUM('Ya', 'Tidak') NULL");
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN family_status ENUM('Ya', 'Tidak') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            //
        });
    }
};
