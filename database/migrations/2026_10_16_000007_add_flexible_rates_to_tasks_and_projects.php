<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->decimal('fixed_amount', 10, 2)->nullable()->after('hourly_rate');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('monthly_retainer_amount', 10, 2)->nullable()->after('fixed_budget_amount');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('fixed_amount');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('monthly_retainer_amount');
        });
    }
};
