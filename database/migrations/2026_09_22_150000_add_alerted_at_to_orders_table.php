<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace l'alerte envoyée à la maison. Sans elle, impossible de rattraper une
 * commande dont la notification s'est perdue — un processus PHP interrompu,
 * une coupure réseau chez Telegram.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('alerted_at')->nullable()->after('whatsapp_status');
            $table->index(['alerted_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['alerted_at', 'created_at']);
            $table->dropColumn('alerted_at');
        });
    }
};
