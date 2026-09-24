<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les avis clients.
 *
 * Un avis peut être rattaché à une commande : c'est ce lien, et lui seul, qui
 * donne la mention « Achat vérifié ». Sans commande livrée derrière, l'avis
 * s'affiche sans badge — la mention ne se saisit pas à la main.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->string('author_name', 120);
            $table->string('city', 80)->nullable();
            $table->unsignedTinyInteger('rating')->comment('1 à 5');
            $table->text('body');
            $table->string('size_bought', 20)->nullable()->comment('Par exemple 32/32');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['published_at', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
