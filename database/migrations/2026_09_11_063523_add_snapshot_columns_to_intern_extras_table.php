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
        Schema::table('intern_extras', function (Blueprint $table) {
            $table->string('letter_number')->nullable()->after('rekomendasi_url');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete()->after('letter_number');
            $table->string('company_name')->nullable()->after('brand_id');
            $table->string('company_address', 500)->nullable()->after('company_name');
            $table->string('company_logo_path')->nullable()->after('company_address');
            $table->string('signatory_name')->nullable()->after('company_logo_path');
            $table->string('signatory_position')->nullable()->after('signatory_name');
            $table->string('signature_image_path')->nullable()->after('signatory_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_extras', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn([
                'letter_number',
                'brand_id',
                'company_name',
                'company_address',
                'company_logo_path',
                'signatory_name',
                'signatory_position',
                'signature_image_path',
            ]);
        });
    }
};
