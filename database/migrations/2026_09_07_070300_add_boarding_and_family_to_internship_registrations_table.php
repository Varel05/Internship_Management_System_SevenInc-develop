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
            $table->enum('boarding_info', ['Kos/Asrama', 'Pulang Pergi'])->nullable();
            $table->enum('family_status', ['Belum Menikah', 'Menikah', 'Bercerai'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->dropColumn(['boarding_info', 'family_status']);
        });
    }
};
