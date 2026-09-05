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
        Schema::create('intern_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_registration_id')->constrained('internship_registrations')->onDelete('cascade');
            $table->string('tool_name', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_tools');
    }
};
