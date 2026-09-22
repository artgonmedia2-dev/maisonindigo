<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une collection peut occuper l'un des deux emplacements de l'accueil.
 * Son visuel et son badge y sont repris tels quels : plus besoin de
 * reverser la même image dans les réglages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->string('home_slot', 10)->nullable()->unique()->after('is_visible');
            $table->string('home_badge', 10)->nullable()->after('home_slot');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->dropUnique(['home_slot']);
            $table->dropColumn(['home_slot', 'home_badge']);
        });
    }
};
