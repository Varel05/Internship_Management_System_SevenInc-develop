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
        Schema::create('webinar_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('webinar_attendances')->onDelete('cascade');
            $table->string('certificate_number', 100)->unique();
            $table->string('company_name')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('company_logo_path')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_position')->nullable();
            $table->string('signature_image_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webinar_certificates');
    }
};
