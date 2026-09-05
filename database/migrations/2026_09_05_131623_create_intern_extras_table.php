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
        Schema::create('intern_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->unique()->constrained('internship_registrations')->onDelete('cascade');
            $table->string('rekomendasi_path')->nullable();
            $table->string('rekomendasi_url')->nullable();
            $table->string('alumni_group_url')->nullable();
            $table->string('alumni_group_label')->default('Grup Alumni Seveninc');
            $table->string('job_info_url')->nullable();
            $table->text('job_info_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_extras');
    }
};
