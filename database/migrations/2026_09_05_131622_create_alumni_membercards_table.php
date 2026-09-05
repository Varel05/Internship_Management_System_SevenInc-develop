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
        Schema::create('alumni_membercards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('internship_registrations')->onDelete('cascade');
            $table->string('member_code', 100)->unique();
            $table->string('batch_year', 10);
            $table->string('model_url')->nullable();
            $table->boolean('has_downloaded')->default(false);
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_membercards');
    }
};
