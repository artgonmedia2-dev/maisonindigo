<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le Journal : les articles informationnels du cluster.
 *
 * Chaque article porte une réponse directe de deux ou trois phrases sous son
 * titre — c'est ce fragment que les moteurs génératifs citent — puis des
 * sections H2, une FAQ et trois produits du catalogue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name', 120);
            $table->string('role', 120)->nullable();
            $table->text('bio')->nullable();
            $table->string('email', 190)->nullable();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt')->comment('« En bref » : la réponse directe, 2 à 3 phrases');
            $table->json('content_blocks')->nullable()->comment('Sections H2 : titre + texte');
            $table->json('faq')->nullable();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['published_at', 'position']);
        });

        Schema::create('article_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);

            $table->unique(['article_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_product');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('authors');
    }
};
