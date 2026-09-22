<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Coupes et lavages deviennent des tables : la maison peut enrichir son
 * vocabulaire depuis le back-office sans passer par le code.
 *
 * Les produits continuent de stocker l'identifiant court (« straight »,
 * « brut ») : les adresses de la boutique, les filtres et les SKU déjà émis
 * restent valables.
 */
return new class extends Migration
{
    /**
     * Le vocabulaire d'origine, repris tel quel pour que les produits
     * existants gardent leur coupe et leur lavage.
     *
     * @var list<array{slug: string, name: string, sku_code: string, genders: list<string>}>
     */
    private array $cuts = [
        ['slug' => 'straight', 'name' => 'Straight', 'sku_code' => 'STR', 'genders' => ['homme', 'femme']],
        ['slug' => 'regular', 'name' => 'Regular', 'sku_code' => 'REG', 'genders' => ['homme']],
        ['slug' => 'slim', 'name' => 'Slim', 'sku_code' => 'SLM', 'genders' => ['homme', 'femme']],
        ['slug' => 'relaxed', 'name' => 'Relaxed', 'sku_code' => 'RLX', 'genders' => ['homme']],
        ['slug' => 'tapered', 'name' => 'Tapered', 'sku_code' => 'TAP', 'genders' => ['homme']],
        ['slug' => 'wide_leg', 'name' => 'Wide Leg', 'sku_code' => 'WID', 'genders' => ['femme']],
        ['slug' => 'mom', 'name' => 'Mom', 'sku_code' => 'MOM', 'genders' => ['femme']],
        ['slug' => 'bootcut', 'name' => 'Bootcut', 'sku_code' => 'BOO', 'genders' => ['femme']],
        ['slug' => 'flare', 'name' => 'Flare', 'sku_code' => 'FLA', 'genders' => ['femme']],
    ];

    /**
     * @var list<array{slug: string, name: string, sku_code: string}>
     */
    private array $washes = [
        ['slug' => 'brut', 'name' => 'Indigo Brut', 'sku_code' => 'BRU'],
        ['slug' => 'stone', 'name' => 'Stone', 'sku_code' => 'STO'],
        ['slug' => 'clair', 'name' => 'Clair', 'sku_code' => 'CLA'],
        ['slug' => 'noir', 'name' => 'Noir', 'sku_code' => 'NOI'],
        ['slug' => 'gris', 'name' => 'Gris', 'sku_code' => 'GRI'],
        ['slug' => 'ecru', 'name' => 'Écru', 'sku_code' => 'ECR'],
    ];

    public function up(): void
    {
        Schema::create('cuts', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 20)->unique()->comment('Repris dans les adresses et les filtres');
            $table->string('name', 60);
            $table->string('sku_code', 3)->comment('Trois lettres dans le SKU');
            $table->json('genders')->comment('Genres auxquels la coupe est proposée');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        Schema::create('washes', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 20)->unique();
            $table->string('name', 60);
            $table->string('sku_code', 3);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        $now = now();
        $genres = array_map(fn (Gender $gender): string => $gender->value, Gender::cases());

        DB::table('cuts')->insert(array_map(fn (array $cut, int $index): array => [
            'slug' => $cut['slug'],
            'name' => $cut['name'],
            'sku_code' => $cut['sku_code'],
            // Un genre inconnu serait ignoré partout : on s'en tient aux genres déclarés.
            'genders' => json_encode(array_values(array_intersect($cut['genders'], $genres))),
            'position' => ($index + 1) * 10,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->cuts, array_keys($this->cuts)));

        DB::table('washes')->insert(array_map(fn (array $wash, int $index): array => [
            'slug' => $wash['slug'],
            'name' => $wash['name'],
            'sku_code' => $wash['sku_code'],
            'position' => ($index + 1) * 10,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->washes, array_keys($this->washes)));
    }

    public function down(): void
    {
        Schema::dropIfExists('washes');
        Schema::dropIfExists('cuts');
    }
};
