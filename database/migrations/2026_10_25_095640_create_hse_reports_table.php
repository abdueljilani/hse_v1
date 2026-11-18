
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hse_reports', function (Blueprint $table) {
            $table->id();

            $table->string('report_number')->unique();
            $table->string('title');
            $table->string('report_type');
            $table->string('location');
            $table->text('description');
            $table->string('risk_level');
            $table->text('corrective_action')->nullable();
            $table->string('status')->default('open');
            $table->dateTime('reported_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_reports');
    }
};
