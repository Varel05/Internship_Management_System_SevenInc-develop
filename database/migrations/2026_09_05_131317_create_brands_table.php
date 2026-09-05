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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('company_address')->nullable();
            $table->string('logo')->nullable();
            $table->string('internship_certificate_bg')->nullable();
            $table->string('webinar_certificate_bg')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_email')->nullable();
            $table->string('signatory_position')->nullable();
            $table->string('signature')->nullable();
            // Note: In the user SQL, there were no timestamps for brands, but standard Laravel uses them. 
            // I'll keep timestamps just in case, but if the SQL explicitly didn't have them, I should omit them. 
            // The SQL for brands does NOT have created_at or updated_at. I will omit them.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
