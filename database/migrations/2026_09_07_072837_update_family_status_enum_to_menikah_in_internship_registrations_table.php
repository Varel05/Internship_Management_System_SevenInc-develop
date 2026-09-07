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
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN family_status VARCHAR(50) NULL");
        DB::statement("UPDATE internship_registrations SET family_status = 'Belum Menikah' WHERE family_status = 'Tidak' OR family_status = 'Belum Menikah'");
        DB::statement("UPDATE internship_registrations SET family_status = 'Sudah Menikah' WHERE family_status = 'Ya' OR family_status = 'Menikah'");
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN family_status ENUM('Belum Menikah', 'Sudah Menikah') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN family_status VARCHAR(50) NULL");
        DB::statement("UPDATE internship_registrations SET family_status = 'Tidak' WHERE family_status = 'Belum Menikah'");
        DB::statement("UPDATE internship_registrations SET family_status = 'Ya' WHERE family_status = 'Sudah Menikah'");
        DB::statement("ALTER TABLE internship_registrations MODIFY COLUMN family_status ENUM('Ya', 'Tidak') NULL");
    }
};
