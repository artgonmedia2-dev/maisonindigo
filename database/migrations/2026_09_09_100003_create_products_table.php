<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Produit = 1 coupe × 1 lavage × 1 genre. Prix en centimes entiers.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title')->comment('{Coupe} {Lavage}, ex. « Straight Indigo Brut »');
            $table->string('gender', 10);
            $table->string('cut', 20);
            $table->string('wash', 20);
            $table->text('description')->nullable();
            $table->unsignedInteger('price')->comment('centimes');
            $table->unsignedInteger('compare_at_price')->nullable()->comment('centimes, ventes privées uniquement');
            $table->string('fabric_origin')->nullable();
            $table->decimal('weight_oz', 4, 1)->nullable();
            $table->string('composition')->nullable();
            $table->unsignedSmallInteger('model_height_cm')->nullable();
            $table->string('model_size', 10)->nullable()->comment('ex. 32/32');
            $table->text('size_advice')->nullable();
            $table->foreignId('size_chart_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 10)->default('draft');
            $table->boolean('is_new')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_atelier')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();

            $table->unique(['gender', 'cut', 'wash']);
            $table->index(['status', 'gender']);
            $table->index(['status', 'is_new']);
            $table->index(['status', 'is_featured']);
        });

        // Variante = taille × longueur. SKU MI-{genre}-{coupe}-{lavage}-{taille}-{longueur}.
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('size')->comment('26 à 42');
            $table->unsignedTinyInteger('length')->comment('30, 32 ou 34');
            $table->string('sku', 40)->unique();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(3);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'size', 'length']);
            $table->index(['product_id', 'stock']);
        });

        Schema::create('product_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_id')->constrained('products')->cascadeOnDelete();
            $table->string('type', 30)->default('complete_look');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_relations');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
