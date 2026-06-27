<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('priority');
            $table->unsignedInteger('response_minutes')->default(0);
            $table->unsignedInteger('resolution_minutes')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
