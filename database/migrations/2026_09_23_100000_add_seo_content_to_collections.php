<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le contenu éditorial des hubs de coupe.
 *
 * Une collection peut devenir le hub d'un couple genre × coupe : elle sert
 * alors /{genre}/jean-{coupe} avec son intro, ses sections et sa FAQ. Les
 * sous-collections par lavage vivent dans leur propre table, une ligne par
 * couple hub × lavage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->string('hub_gender', 10)->nullable()->after('home_badge');
            $table->string('hub_cut', 20)->nullable()->after('hub_gender');
            $table->text('intro')->nullable()->after('description')->comment('80 à 120 mots, au-dessus de la grille');
            $table->json('content_blocks')->nullable()->after('intro')->comment('Sections H2 : titre + texte');
            $table->json('faq')->nullable()->after('content_blocks')->comment('Questions et réponses, schema FAQPage');

            $table->unique(['hub_gender', 'hub_cut']);
        });

        Schema::create('collection_washes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('wash', 20);
            $table->text('intro')->nullable()->comment('60 à 80 mots, propres au lavage');
            $table->json('faq')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['collection_id', 'wash']);
            $table->index(['is_visible', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_washes');

        Schema::table('collections', function (Blueprint $table): void {
            $table->dropUnique(['hub_gender', 'hub_cut']);
            $table->dropColumn(['hub_gender', 'hub_cut', 'intro', 'content_blocks', 'faq']);
        });
    }
};
