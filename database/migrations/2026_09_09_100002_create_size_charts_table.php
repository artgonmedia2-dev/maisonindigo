<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_charts', function (Blueprint $table) {
            $table->id();
            $table->string('gender', 10);
            $table->string('cut', 20);
            $table->string('title');
            $table->timestamps();

            $table->unique(['gender', 'cut']);
        });

        Schema::create('size_chart_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_chart_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('size')->comment('26 à 42');
            $table->decimal('waist_cm', 5, 1);
            $table->decimal('hips_cm', 5, 1);
            $table->decimal('thigh_cm', 5, 1);
            $table->decimal('inseam_30', 5, 1);
            $table->decimal('inseam_32', 5, 1);
            $table->decimal('inseam_34', 5, 1);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['size_chart_id', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_chart_rows');
        Schema::dropIfExists('size_charts');
    }
};
