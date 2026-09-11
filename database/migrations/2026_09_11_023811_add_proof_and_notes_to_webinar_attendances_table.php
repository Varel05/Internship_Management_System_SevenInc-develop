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
        Schema::table('webinar_attendances', function (Blueprint $table) {
            $table->string('proof_file')->nullable()->after('status');
            $table->text('proof_note')->nullable()->after('proof_file');
            $table->text('rejection_reason')->nullable()->after('proof_note');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('rejection_reason');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->foreignId('certificate_id')->nullable()->constrained('webinar_certificates')->nullOnDelete()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webinar_attendances', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['certificate_id']);
            $table->dropColumn([
                'proof_file', 
                'proof_note', 
                'rejection_reason', 
                'reviewed_by', 
                'reviewed_at', 
                'certificate_id'
            ]);
        });
    }
};
