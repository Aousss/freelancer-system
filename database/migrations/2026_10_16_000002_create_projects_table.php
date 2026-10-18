<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('billing_type', 30)->default('hourly');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('fixed_budget_amount', 10, 2)->nullable();
            $table->decimal('budget_hours', 6, 2)->nullable();
            $table->string('color', 20)->default('#8b5cf6');
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
