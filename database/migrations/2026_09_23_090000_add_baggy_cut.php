<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La coupe baggy entre au catalogue, pour les deux genres.
 *
 * C'est la tête du cluster « jean baggy homme » : elle ouvre le hub
 * /homme/jean-baggy et ses sous-collections par lavage.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('cuts')->where('slug', 'baggy')->exists()) {
            return;
        }

        DB::table('cuts')->insert([
            'slug' => 'baggy',
            'name' => 'Baggy',
            'sku_code' => 'BAG',
            'genders' => json_encode([Gender::Homme->value, Gender::Femme->value]),
            // Avant Straight : c'est la coupe mise en avant cette saison.
            'position' => 5,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Une coupe employée par un produit ne se supprime pas.
        DB::table('cuts')
            ->where('slug', 'baggy')
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))->from('products')->whereColumn('products.cut', 'cuts.slug'))
            ->delete();
    }
};
