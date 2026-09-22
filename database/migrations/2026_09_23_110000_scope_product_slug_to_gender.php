<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Les produits passent sous /{genre}/{slug}.
 *
 * Le genre étant porté par l'adresse, il n'a plus à être répété dans le slug :
 * « /homme/baggy-indigo-brut » plutôt que « /produit/baggy-indigo-brut-homme ».
 * L'unicité devient donc genre × slug, et les slugs existants perdent leur
 * suffixe quand la place est libre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->unique(['gender', 'slug']);
        });

        foreach (DB::table('products')->select('id', 'gender', 'slug')->get() as $product) {
            $court = Str::of($product->slug)->beforeLast('-'.$product->gender)->value();

            if ($court === '' || $court === $product->slug) {
                continue;
            }

            $pris = DB::table('products')
                ->where('gender', $product->gender)
                ->where('slug', $court)
                ->where('id', '!=', $product->id)
                ->exists();

            if (! $pris) {
                DB::table('products')->where('id', $product->id)->update(['slug' => $court]);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('products')->select('id', 'gender', 'slug')->get() as $product) {
            if (! str_ends_with($product->slug, '-'.$product->gender)) {
                DB::table('products')->where('id', $product->id)->update(['slug' => $product->slug.'-'.$product->gender]);
            }
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['gender', 'slug']);
            $table->unique(['slug']);
        });
    }
};
