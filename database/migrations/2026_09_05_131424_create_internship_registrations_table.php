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
        Schema::create('internship_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('fullname', 150);
            $table->date('born_date');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->string('phone_number', 20);
            $table->foreignId('city_id')->constrained('cities');
            $table->string('student_id', 50);
            $table->foreignId('institution_id')->constrained('institutions');
            $table->foreignId('faculty_id')->constrained('faculties');
            $table->foreignId('study_program_id')->constrained('study_programs');
            $table->text('internship_reason');
            $table->enum('internship_type', ['Kampus Merdeka', 'Magang Kampus', 'Magang Mandiri', 'PKL']);
            $table->enum('internship_arrangement', ['Remote', 'Onsite', 'Hibrida']);
            $table->enum('current_status', ['Mahasiswa/Pelajar', 'Lulusan Baru', 'Karyawan', 'Tidak Bekerja']);
            $table->enum('english_book_ability', ['Saya bisa', 'Kurang bisa', 'Tidak bisa']);
            $table->foreignId('division_id')->constrained('divisions');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->enum('internship_status', ['new', 'waiting', 'active', 'pending', 'completed', 'exited', 'accepted', 'rejected'])->default('new');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('supervisor_contact', 20)->nullable();
            $table->string('supervisor_name', 255)->nullable();
            $table->string('parent_wa_contact', 20)->nullable();
            $table->string('parent_name', 255)->nullable();
            $table->text('current_activities')->nullable();
            $table->string('social_media_instagram', 255)->nullable();
            $table->string('profile_photo')->nullable();
            $table->enum('boarding_info', ['Ya', 'Tidak'])->nullable();
            $table->enum('family_status', ['Belum Menikah', 'Sudah Menikah'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_registrations');
    }
};
