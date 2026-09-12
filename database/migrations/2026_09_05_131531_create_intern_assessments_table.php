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
        Schema::create('intern_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('internship_registrations')->onDelete('cascade');
            $table->string('assessment_number')->nullable();
            $table->json('aspek_penilaian');
            $table->double('rata_rata', 8, 2)->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
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
        Schema::dropIfExists('intern_assessments');
    }
};
