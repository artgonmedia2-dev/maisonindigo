<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membres de la maison (newsletter, ventes privées), synchronisés avec MailerLite.
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('source', 30)->nullable()->comment('footer, checkout, quiz, popup');
            $table->string('mailerlite_id')->nullable()->index();
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
