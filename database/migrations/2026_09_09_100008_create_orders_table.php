<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Compteur séquentiel par année pour les numéros MI-{AAAA}-{NNNNNN}.
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_number')->default(0);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique()->comment('MI-2026-000123');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('new');
            $table->string('payment_method', 10);
            $table->unsignedInteger('subtotal')->comment('centimes');
            $table->unsignedInteger('discount_total')->default(0)->comment('centimes');
            $table->unsignedInteger('shipping_total')->default(0)->comment('centimes');
            $table->unsignedInteger('total')->comment('centimes');
            $table->char('currency', 3)->default('MAD');
            $table->json('shipping_address')->comment('snapshot : name, phone, line1, line2, city, region, zone');
            $table->string('discount_code', 30)->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('notes')->nullable()->comment('notes internes');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('whatsapp_status', 20)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->comment('snapshot');
            $table->unsignedTinyInteger('size');
            $table->unsignedTinyInteger('length');
            $table->string('sku', 40);
            $table->unsignedSmallInteger('qty');
            $table->unsignedInteger('unit_price')->comment('centimes, snapshot');
            $table->unsignedInteger('total')->comment('centimes');
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_sequences');
    }
};
