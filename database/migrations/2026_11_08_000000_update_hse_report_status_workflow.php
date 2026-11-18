<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hse_reports')
            ->where('status', 'open')
            ->update(['status' => 'in_progress']);

        Schema::table('hse_reports', function (Blueprint $table) {
            $table->string('status')->default('in_progress')->change();
        });
    }

    public function down(): void
    {
        Schema::table('hse_reports', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }
};
