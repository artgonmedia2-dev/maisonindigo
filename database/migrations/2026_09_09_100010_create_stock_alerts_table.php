<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // « Me prévenir » sur une taille en rupture.
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['product_variant_id', 'notified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
