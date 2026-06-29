<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_schedules', function (Blueprint $table) {
            // true = masih berupa slot yang ditawarkan, false = sudah dipilih kandidat
            $table->boolean('is_proposed')->default(true)->after('notes');
            // Token untuk kandidat quick-apply (tanpa login)
            $table->string('selection_token', 64)->nullable()->unique()->after('is_proposed');
        });
    }

    public function down(): void
    {
        Schema::table('interview_schedules', function (Blueprint $table) {
            $table->dropColumn(['is_proposed', 'selection_token']);
        });
    }
};
