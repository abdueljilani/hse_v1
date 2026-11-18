<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hse_report_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hse_report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('report_number');
            $table->string('old_status');
            $table->string('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('changed_by_name');
            $table->timestamps();

            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_report_status_histories');
    }
};
