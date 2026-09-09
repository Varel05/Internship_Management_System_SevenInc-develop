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
        Schema::create('skl_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intern_id');
            $table->string('skl_number');
            $table->string('company_name');
            $table->string('company_logo_path')->nullable();
            $table->string('signatory_name');
            $table->string('signatory_position')->nullable();
            $table->string('signature_image_path')->nullable();
            $table->timestamps();

            $table->foreign('intern_id')->references('id')->on('internship_registrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skl_documents');
    }
};
